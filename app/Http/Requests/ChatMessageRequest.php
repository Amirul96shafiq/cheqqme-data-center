<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|max:1000',
            'conversation_id' => 'nullable|string',
            'persona' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Please enter a message.',
            'message.string' => 'Message must be text.',
            'message.max' => 'Message is too long.',
        ];
    }
}
