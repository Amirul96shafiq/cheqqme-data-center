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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:700'],
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'attachments' => ['required', 'array', 'min:1', 'max:5'],
            'attachments.*' => [
                'file',
                'max:20480', // 20MB in kilobytes
                'mimes:jpg,jpeg,png,pdf,mp4',
            ],
        ];
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
            'email.required' => 'Please provide your email address.',
            'email.email' => 'Please provide a valid email address.',
            'title.required' => 'Please provide a title for the issue.',
            'project_id.required' => 'Project is required.',
            'project_id.exists' => 'The selected project does not exist.',
            'description.required' => 'Description is required.',
            'attachments.required' => 'Please attach at least one file.',
            'attachments.min' => 'Please attach at least one file.',
            'attachments.max' => 'You can upload a maximum of 5 files.',
            'attachments.*.max' => 'Each file must not exceed 20MB.',
            'attachments.*.mimes' => 'Only JPG, JPEG, PNG, PDF, and MP4 files are allowed.',
        ];
    }
}
