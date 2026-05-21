<?php

namespace App\Http\Requests\API;

use App\Models\Payout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GetPayoutAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Keep only the fields wanted
        $this->replace($this->only(['status', 'filter_by_date', 'skip', 'limit', 'per_page', 'page', 'show_transactions', 'show_timeline']));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Payout::$rules_listed;
    }
}
