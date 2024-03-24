<?php

namespace Fintech\Ekyc\Http\Requests;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'mobile' => ['nullable', 'string', 'min:10'],
            'email' => ['nullable', 'string', 'email:rfc,dns', 'min:2', 'max:255'],
            'father_name' => ['string', 'nullable'],
            'mother_name' => ['string', 'nullable'],
            'gender' => ['string', 'nullable'],
            'marital_status' => ['string', 'nullable'],
            'occupation' => ['string', 'nullable'],
            'source_of_income' => ['string', 'nullable'],
            'id_type' => ['string', 'nullable'],
            'id_doc_type_id' => ['integer', 'required'],
            'id_no' => ['string', 'nullable'],
            'id_issue_country' => ['string', 'nullable'],
            'id_expired_at' => ['string', 'nullable'],
            'id_issue_at' => ['string', 'nullable'],
            'photo' => ['string', 'nullable'],
            'documents' => ['array', 'required', 'min:1'],
            'documents.*.type' => ['string', 'required'],
            'documents.*.back' => ['string', 'required_without:documents.*.front'],
            'documents.*.front' => ['string', 'required_without:documents.*.back'],
            'employer' => ['array', 'nullable'],
            'employer.company_name' => ['string', 'nullable'],
            'employer.company_address' => ['string', 'nullable'],
            'employer.company_registration_number' => ['string', 'nullable'],
            'proof_of_address' => ['array', 'required', 'min:1'],
            'proof_of_address.*.type' => ['string', 'required'],
            'proof_of_address.*.front' => ['string', 'required_without:proof_of_address.*.back'],
            'proof_of_address.*.back' => ['string', 'required_without:proof_of_address.*.front'],
            'date_of_birth' => ['date', 'nullable'],
            'permanent_address' => ['string', 'nullable'],
            'permanent_city_id' => ['integer', 'nullable'],
            'permanent_state_id' => ['integer', 'nullable'],
            'permanent_country_id' => ['integer', 'nullable'],
            'permanent_post_code' => ['string', 'nullable'],
            'present_address' => ['string', 'nullable'],
            'present_city_id' => ['integer', 'nullable'],
            'present_state_id' => ['integer', 'nullable'],
            'present_country_id' => ['integer', 'nullable'],
            'present_post_code' => ['string', 'nullable'],
            'nationality' => ['string', 'nullable'],
        ];
    }

    protected function prepareForValidation()
    {
        dd($this->all());
    }
}
