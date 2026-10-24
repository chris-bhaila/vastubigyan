<?php

namespace App\Http\Requests;

use App\Services\AnalysisValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreAnalysisStep2Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return AnalysisValidationRules::step2() + [
            'confirm' => ['accepted'],
            'request_key' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm.accepted' => 'You must confirm these details before submitting.',
        ];
    }
}
