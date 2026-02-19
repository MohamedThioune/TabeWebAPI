<?php

namespace App\Http\Requests\API;

use App\Models\GiftCard;
use InfyOm\Generator\Request\APIRequest;

class UpdateGiftCardAPIRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function prepareForValidation()
    {
        // Keep only the fields wanted
        $this->replace($this->only(['type', 'face_amount', 'expired_at', 'issued_via', 'design_id']));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = GiftCard::$rules_updated;
        
        return $rules;
    }
}
