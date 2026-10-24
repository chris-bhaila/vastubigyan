<?php

namespace App\Http\Controllers;

use App\Enums\AnalysisStatus;
use App\Exceptions\InsufficientCreditsException;
use App\Http\Requests\StoreAnalysisStep1Request;
use App\Http\Requests\StoreAnalysisStep2Request;
use App\Models\Analysis;
use App\Models\AnalysisInput;
use App\Services\AnalysisValidationRules;
use App\Services\CreditService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AnalysisController extends Controller
{
    /**
     * The session key the two-step intake wizard keeps its state under.
     */
    private const SESSION_KEY = 'analysis.wizard';

    public function __construct(private CreditService $credits) {}

    /**
     * The user's own analyses, newest first.
     */
    public function index(Request $request): View
    {
        $analyses = $request->user()->analyses()->latest()->paginate(15);

        return view('analyses.index', ['analyses' => $analyses]);
    }

    public function show(Analysis $analysis): View
    {
        return view('analyses.show', [
            'analysis' => $analysis->load('input'),
        ]);
    }

    /**
     * Stream one of an analysis's two uploaded files from the private
     * disk. The owner or an admin may fetch either file; everyone else
     * gets 403. There is no public URL for these files.
     */
    public function file(Request $request, Analysis $analysis, string $field): mixed
    {
        abort_unless($request->user()->can('view', $analysis), 403);

        $column = match ($field) {
            'plot-with-road' => 'plot_with_road',
            'floor-plan' => 'floor_plan',
            default => abort(404),
        };

        $path = $analysis->input?->{$column};

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    /**
     * Step 1 of the intake wizard: private information. Generates the
     * submission's request_key on first visit and keeps it in the session
     * for the rest of the wizard, so it survives across both steps and
     * makes the final submit idempotent.
     */
    public function create(Request $request): View
    {
        $wizard = $request->session()->get(self::SESSION_KEY, []);

        if (! isset($wizard['request_key'])) {
            $wizard['request_key'] = (string) Str::uuid();
            $request->session()->put(self::SESSION_KEY, $wizard);
        }

        return view('analyses.step1', [
            'old' => $wizard['step1'] ?? [],
        ]);
    }

    /**
     * Validate step 1 and stash it in the session. Nothing is written to
     * the database yet and no credit is spent here.
     */
    public function storeStep1(StoreAnalysisStep1Request $request): RedirectResponse
    {
        $wizard = $request->session()->get(self::SESSION_KEY, []);
        $wizard['step1'] = $request->validated();
        $wizard['request_key'] ??= (string) Str::uuid();

        $request->session()->put(self::SESSION_KEY, $wizard);

        return redirect()->route('analyses.step2');
    }

    /**
     * Step 2 of the intake wizard: land information. Requires step 1 to
     * already be in the session; otherwise the user is sent back to it.
     */
    public function step2(Request $request): View|RedirectResponse
    {
        $wizard = $request->session()->get(self::SESSION_KEY, []);

        if (! isset($wizard['step1'])) {
            return redirect()->route('analyses.create');
        }

        return view('analyses.step2', [
            'requestKey' => $wizard['request_key'],
        ]);
    }

    /**
     * Final submit: validates step 2, re-validates step 1 from the session
     * (never trusted blindly), stores the two files, then in one DB
     * transaction creates the Analysis, its immutable inputs row, and
     * spends one credit. Any failure rolls back the transaction and
     * deletes any files already written — no analysis is ever left
     * without its inputs, and no credit is ever spent without both.
     *
     * The request_key travels as a hidden field (sourced from the session
     * when step 2 is rendered) rather than being read only from the
     * session, so a genuine double submission is still caught even if the
     * first request already cleared the session.
     */
    public function store(StoreAnalysisStep2Request $request): RedirectResponse
    {
        $user = $request->user();
        $requestKey = $request->input('request_key');

        if (! $requestKey) {
            return redirect()->route('analyses.create');
        }

        $existing = Analysis::query()->where('request_key', $requestKey)->first();

        if ($existing) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('analyses.show', $existing);
        }

        $step1 = $request->session()->get(self::SESSION_KEY.'.step1');

        if (! $step1) {
            return redirect()->route('analyses.create');
        }

        // Never trust the session blindly: re-validate step 1 for real.
        try {
            $step1Validator = Validator::make($step1, AnalysisValidationRules::step1());
            $step1Validator->after(fn ($validator) => AnalysisValidationRules::afterStep1($validator, $step1));
            $step1 = $step1Validator->validate();
        } catch (ValidationException) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('analyses.create')
                ->with('status', 'Your saved details were invalid. Please start again.');
        }

        $step2 = Arr::except($request->validated(), ['plot_with_road', 'floor_plan', 'confirm', 'request_key']);

        $plotWithRoadPath = $this->storeUploadedFile($request->file('plot_with_road'), $user->id);
        $floorPlanPath = $this->storeUploadedFile($request->file('floor_plan'), $user->id);

        $inputData = array_merge($step1, $step2, [
            'plot_with_road' => $plotWithRoadPath,
            'floor_plan' => $floorPlanPath,
        ]);

        try {
            $analysis = DB::transaction(function () use ($user, $requestKey, $inputData, $plotWithRoadPath, $floorPlanPath) {
                $analysis = Analysis::create([
                    'user_id' => $user->id,
                    'status' => AnalysisStatus::Started,
                    'request_key' => $requestKey,
                ]);

                AnalysisInput::create($inputData + [
                    'analysis_id' => $analysis->id,
                    'integrity_hash' => AnalysisInput::computeIntegrityHash($inputData, $plotWithRoadPath, $floorPlanPath),
                ]);

                $transaction = $this->credits->consume($user, $analysis->id);

                $analysis->update(['credit_transaction_id' => $transaction->id]);

                return $analysis;
            });
        } catch (QueryException $e) {
            $this->deleteFiles($plotWithRoadPath, $floorPlanPath);

            // Lost a double-click/race on the same request_key to another
            // request that already created the analysis.
            $existing = Analysis::query()->where('request_key', $requestKey)->first();

            if ($existing) {
                $request->session()->forget(self::SESSION_KEY);

                return redirect()->route('analyses.show', $existing);
            }

            throw $e;
        } catch (InsufficientCreditsException) {
            $this->deleteFiles($plotWithRoadPath, $floorPlanPath);

            return redirect()->route('pricing')
                ->with('status', 'You have no analyses left. Buy more to continue.');
        } catch (Throwable $e) {
            $this->deleteFiles($plotWithRoadPath, $floorPlanPath);

            throw $e;
        }

        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('analyses.show', $analysis)
            ->with('status', 'Analysis submitted.');

        // ---------------------------------------------------------------
        // Stub: the real Vastu analysis work is not implemented yet. Hook
        // it in here, inline or via a dispatched job. If it fails on our
        // side, restore the spent credit and mark the analysis failed
        // instead of leaving the user short a credit for nothing:
        //
        //     try {
        //         app(VastuAnalysisRunner::class)->run($analysis);
        //         $analysis->update(['status' => AnalysisStatus::Completed]);
        //     } catch (\Throwable $e) {
        //         $this->credits->restoreForAnalysis($analysis);
        //         $analysis->update(['status' => AnalysisStatus::Failed]);
        //     }
        // ---------------------------------------------------------------
    }

    /**
     * Store an uploaded file under a random name on the private disk.
     * Never overwrites: the uuid name is generated fresh every call.
     */
    private function storeUploadedFile(UploadedFile $file, int $userId): string
    {
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();

        return $file->storeAs("analysis-files/{$userId}", $filename, 'local');
    }

    private function deleteFiles(string ...$paths): void
    {
        Storage::disk('local')->delete($paths);
    }
}
