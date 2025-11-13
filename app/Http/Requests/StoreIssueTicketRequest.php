<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIssueTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow public access - no authentication required
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'communication_preference' => ['required', 'string', 'in:email,whatsapp,both'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:700'],
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'temp_file_ids' => ['required', 'array', 'min:1', 'max:5'],
            'temp_file_ids.*' => ['required', 'string', 'uuid'],
            'search_confirmation' => ['required', 'accepted'],
        ];

        // Conditional validation based on communication preference
        if ($this->input('communication_preference') === 'email') {
            $rules['email'] = ['required', 'email', 'max:255'];
        } elseif ($this->input('communication_preference') === 'whatsapp') {
            $rules['whatsapp_number'] = [
                'required',
                'string',
                'regex:/^\+[1-9]\d{7,14}$/', // E.164 style: + and 8-15 digits total
            ];
        } elseif ($this->input('communication_preference') === 'both') {
            $rules['email'] = ['required', 'email', 'max:255'];
            $rules['whatsapp_number'] = [
                'required',
                'string',
                'regex:/^\+[1-9]\d{7,14}$/', // E.164 style: + and 8-15 digits total
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide your name.',
            'communication_preference.required' => 'Please select your preferred communication method.',
            'communication_preference.in' => 'Please select Email, WhatsApp, or Both.',
            'email.required' => 'Please provide your email address.',
            'email.email' => 'Please provide a valid email address.',
            'whatsapp_number.required' => 'Please provide your WhatsApp number.',
            'whatsapp_number.regex' => 'Enter a valid WhatsApp number with + and country code (e.g., +60123456789).',
            'title.required' => 'Please provide a title for the issue.',
            'project_id.required' => 'Project is required.',
            'project_id.exists' => 'The selected project does not exist.',
            'description.required' => 'Description is required.',
            'temp_file_ids.required' => 'Please attach at least one file.',
            'temp_file_ids.min' => 'Please attach at least one file.',
            'temp_file_ids.max' => 'You can upload a maximum of 5 files.',
            'temp_file_ids.*.uuid' => 'Invalid file reference.',
            'search_confirmation.required' => 'Please confirm that you have searched for similar issues.',
            'search_confirmation.accepted' => 'Please confirm that you have searched for similar issues.',
        ];
    }
}
