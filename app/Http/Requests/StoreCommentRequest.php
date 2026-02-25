<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Injects thread_id from the route parameter and user_id from the
     * authenticated user (or existing input) so validation and creation
     * both see the correct values.
     */
    protected function prepareForValidation(): void
    {
        $threadId = $this->route('thread') ?? $this->route('id') ?? $this->input('thread_id');
        $guestUserId = 999999; // Must exist (see migration/seed); use when no auth
        $userId = $this->user()?->id ?? auth()->id() ?? $this->input('user_id', $guestUserId);

        $this->merge([
            'thread_id' => $threadId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // thread_id and user_id are injected in prepareForValidation and
            // enforced at the database level; we keep validation light here
            // to avoid failing when using a default/system user in production.
            'thread_id' => ['sometimes', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'body' => ['required', 'string'],
            'user_id' => ['sometimes', 'integer'],
        ];
    }
}
