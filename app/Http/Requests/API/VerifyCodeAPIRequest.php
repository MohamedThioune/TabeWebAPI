<?php

namespace App\Http\Requests\API;

use App\Models\GiftCard;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyCodeAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        // $this->merge([
        //     'code' => $this->route('code'),
        // ]);

        // Keep only the fields wanted
        $this->replace($this->only(['code']));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return GiftCard::verifyRules();
    }
}
