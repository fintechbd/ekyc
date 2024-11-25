<?php

namespace Fintech\Ekyc\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class KycVerificationRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc,dns', 'min:2', 'max:255'],
            'id_type' => ['string', 'nullable'],
            'id_doc_type_id' => ['integer', 'required'],
            'id_issue_country' => ['string', 'nullable'],
            'photo' => ['string', 'required'],
            'documents' => ['array', 'required', 'min:1'],
            'documents.*.type' => ['string', 'required'],
            'documents.*.back' => ['nullable', 'string', 'required_without:documents.*.front'],
            'documents.*.front' => ['nullable', 'string', 'required_without:documents.*.back'],
            //            'proof_of_address' => ['array', 'required', 'min:1'],
            //            'proof_of_address.*.type' => ['string', 'required'],
            //            'proof_of_address.*.front' => ['string', 'required_without:proof_of_address.*.back'],
            //            'proof_of_address.*.back' => ['string', 'required_without:proof_of_address.*.front'],
        ];
    }
}
