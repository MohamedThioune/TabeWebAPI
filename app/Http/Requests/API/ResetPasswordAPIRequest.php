<?php

namespace App\Http\Requests\API;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordAPIRequest extends FormRequest
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
        // Keep only the fields wanted
        $this->replace($this->only(['otp_code', 'new_password', 'new_password_confirmation']));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return User::$resetPassword;
    }
}
