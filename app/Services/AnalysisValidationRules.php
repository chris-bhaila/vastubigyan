<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

/**
 * The validation rules for each wizard step, shared between the Form
 * Request that validates a live submission and the controller's
 * re-validation of the step 1 data pulled back out of the session at
 * final submit — one source of truth so the two can never drift apart.
 */
class AnalysisValidationRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function step1(): array
    {
        $currentYear = (int) now()->format('Y');

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(config('analysis.genders'))],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s-]{7,15}$/'],
            'contact_address' => ['required', 'string', 'max:255'],
            'birth_year' => ['required', 'integer', 'min:'.config('analysis.birth_year_min'), 'max:'.$currentYear],
            'birth_month' => ['required', 'integer', 'between:1,12'],
            'birth_day' => ['required', 'integer', 'between:1,31'],
            'time_of_birth' => ['required', 'date_format:H:i'],
            'nachhetra' => ['required', Rule::in(config('analysis.nakshatras'))],
        ];
    }

    /**
     * Cross-field checks that plain rules can't express: the three birth_*
     * fields must form a real calendar date, and it can't be in the future.
     *
     * @param  array<string, mixed>  $data
     */
    public static function afterStep1(Validator $validator, array $data): void
    {
        $year = (int) ($data['birth_year'] ?? 0);
        $month = (int) ($data['birth_month'] ?? 0);
        $day = (int) ($data['birth_day'] ?? 0);

        if (! $year || ! $month || ! $day) {
            return;
        }

        if (! checkdate($month, $day, $year)) {
            $validator->errors()->add('birth_day', 'The date of birth is not a real calendar date.');

            return;
        }

        if (Carbon::createFromDate($year, $month, $day)->startOfDay()->isFuture()) {
            $validator->errors()->add('birth_year', 'The date of birth cannot be in the future.');
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function step2(): array
    {
        $currentYear = (int) now()->format('Y');
        $mimes = implode(',', config('analysis.files.mimes'));
        $maxKb = config('analysis.files.max_kb');
        $decimal = 'regex:/^\d{1,8}(\.\d{1,2})?$/';

        return [
            'country' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'municipality' => ['required', 'string', 'max:100'],
            'ward' => ['required', 'integer', 'between:1,99'],
            'village' => ['required', 'string', 'max:100'],
            'tole' => ['required', 'string', 'max:100'],
            'plot_area_sqft' => ['required', 'numeric', 'gt:0', $decimal],
            'length' => ['required', 'numeric', 'gt:0', $decimal],
            'width' => ['required', 'numeric', 'gt:0', $decimal],
            'plinth_area' => ['required', 'numeric', 'gt:0', $decimal],
            'angula_length_inches' => ['required', 'numeric', 'between:0.01,10'],
            'visit_date' => ['required', 'date'],
            'silanyas_month' => ['required', 'integer', 'between:1,12'],
            'constructed_year' => ['required', 'integer', 'min:'.config('analysis.constructed_year_min'), 'max:'.$currentYear],
            'plot_with_road' => ['required', 'image', 'mimes:'.$mimes, 'max:'.$maxKb],
            'floor_plan' => ['required', 'image', 'mimes:'.$mimes, 'max:'.$maxKb],
        ];
    }
}
