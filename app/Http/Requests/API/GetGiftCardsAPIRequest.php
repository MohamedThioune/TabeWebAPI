<?php

namespace App\Http\Requests\API;

use App\Models\GiftCard;
use Illuminate\Foundation\Http\FormRequest;

class GetGiftCardsAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sanitize or filter request input before validation
     */
    protected function prepareForValidation(): void
    {
        // Keep only the fields wanted
        $this->replace($this->only(['status', 'belonging_type', 'skip', 'limit', 'with_summary']));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return GiftCard::$rules_listed;
    }
}
