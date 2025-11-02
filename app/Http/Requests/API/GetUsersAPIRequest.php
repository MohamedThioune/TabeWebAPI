<?php

namespace App\Http\Requests\API;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class GetUsersAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation() : void
    {
       //Keep only the fields wanted
        $this->replace($this->only(['type', 'sector', 'q', 'is_active', 'is_phone_verified', 'city', 'country', 'address', 'skip', 'limit', 'per_page', 'page']));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return User::ruleListed();
    }
}
