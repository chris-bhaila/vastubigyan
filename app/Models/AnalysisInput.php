<?php

namespace App\Models;

use App\Exceptions\AnalysisInputIsImmutableException;
use Carbon\Carbon;
use Database\Factories\AnalysisInputFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A single, immutable snapshot of everything a user submitted for an
 * analysis. Rows are created once, alongside their Analysis, and never
 * change again — see the model-event guards below.
 */
#[Fillable([
    'analysis_id', 'first_name', 'last_name', 'gender', 'phone', 'contact_address',
    'birth_year', 'birth_month', 'birth_day', 'time_of_birth', 'nachhetra',
    'country', 'state', 'district', 'municipality', 'ward', 'village', 'tole',
    'plot_area_sqft', 'length', 'width', 'plinth_area', 'angula_length_inches',
    'visit_date', 'silanyas_month', 'constructed_year',
    'plot_with_road', 'floor_plan', 'integrity_hash',
])]
class AnalysisInput extends Model
{
    /** @use HasFactory<AnalysisInputFactory> */
    use HasFactory;

    /**
     * The ledger-like inputs row is append-only: there is no updated_at.
     */
    const UPDATED_AT = null;

    /**
     * The field names hashed into integrity_hash, in a fixed order so the
     * canonical JSON is deterministic regardless of attribute order.
     */
    private const HASHED_FIELDS = [
        'first_name', 'last_name', 'gender', 'phone', 'contact_address',
        'birth_year', 'birth_month', 'birth_day', 'time_of_birth', 'nachhetra',
        'country', 'state', 'district', 'municipality', 'ward', 'village', 'tole',
        'plot_area_sqft', 'length', 'width', 'plinth_area', 'angula_length_inches',
        'visit_date', 'silanyas_month', 'constructed_year',
        'plot_with_road', 'floor_plan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_year' => 'integer',
            'birth_month' => 'integer',
            'birth_day' => 'integer',
            'ward' => 'integer',
            'silanyas_month' => 'integer',
            'constructed_year' => 'integer',
            'plot_area_sqft' => 'decimal:2',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'plinth_area' => 'decimal:2',
            'angula_length_inches' => 'decimal:2',
            'visit_date' => 'date',
        ];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }

    /**
     * Recompute the integrity hash from this row's current field values and
     * the files currently stored at its paths, and compare it against the
     * hash recorded at creation. A mismatch means either the row or one of
     * its files was changed outside the normal, guarded creation path.
     */
    public function verifyIntegrity(): bool
    {
        $hash = static::computeIntegrityHash(
            $this->attributesToArray(),
            $this->plot_with_road,
            $this->floor_plan,
        );

        return hash_equals($this->integrity_hash, $hash);
    }

    /**
     * SHA-256 over a canonical, deterministically-formatted serialization
     * of every input field, plus the SHA-256 of each stored file's bytes.
     * Computed once at creation (see AnalysisController) and recomputed
     * identically by verifyIntegrity() — normalization here must stay
     * consistent across both raw pre-insert values and casted attributes
     * read back from the model, which is why dates/decimals/times are
     * re-parsed rather than compared as raw strings.
     *
     * @param  array<string, mixed>  $values
     */
    public static function computeIntegrityHash(array $values, string $plotWithRoadPath, string $floorPlanPath): string
    {
        $canonical = [];

        foreach (self::HASHED_FIELDS as $field) {
            $canonical[$field] = self::normalize($field, $values[$field] ?? null);
        }

        $json = json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $fileHashes = hash_file('sha256', Storage::disk('local')->path($plotWithRoadPath))
            .hash_file('sha256', Storage::disk('local')->path($floorPlanPath));

        return hash('sha256', $json.$fileHashes);
    }

    private static function normalize(string $field, mixed $value): string
    {
        return match ($field) {
            'birth_year', 'birth_month', 'birth_day', 'ward', 'silanyas_month', 'constructed_year' => (string) (int) $value,
            'plot_area_sqft', 'length', 'width', 'plinth_area', 'angula_length_inches' => number_format((float) $value, 2, '.', ''),
            'visit_date' => Carbon::parse($value)->format('Y-m-d'),
            'time_of_birth' => Carbon::parse($value)->format('H:i:s'),
            default => (string) $value,
        };
    }

    /**
     * Guard the immutable row against updates and deletes.
     *
     * IMPORTANT: these model events only fire for Eloquent instance writes
     * (->save(), ->update(), ->delete()). They do NOT fire for query-builder
     * or mass writes (AnalysisInput::query()->update(...)/delete(), or
     * DB::table('analysis_inputs')->...). Nothing in this codebase may use
     * those forms against this table — there is no other backstop.
     */
    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new AnalysisInputIsImmutableException;
        });

        static::deleting(function (): void {
            throw new AnalysisInputIsImmutableException;
        });
    }
}
