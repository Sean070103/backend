<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoteRequest extends FormRequest
{
    private const VOTEABLE_MAP = [
        'thread' => \App\Models\Thread::class,
        'comment' => \App\Models\Comment::class,
        'protocol' => \App\Models\Protocol::class,
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Accepts short voteable_type (thread, comment, protocol) and injects user_id when missing.
     */
    protected function prepareForValidation(): void
    {
        $type = $this->input('voteable_type');
        $mapped = is_string($type) && isset(self::VOTEABLE_MAP[$type])
            ? self::VOTEABLE_MAP[$type]
            : $type;

        // Use a dedicated \"guest\" user id when no authenticated user is present,
        // so seeded votes (real users) remain separate and a new click clearly
        // adds or flips a single additional vote in the total.
        $guestUserId = 999999;
        $userId = $this->user()?->id ?? auth()->id() ?? $this->input('user_id', $guestUserId);

        $this->merge([
            'voteable_type' => $mapped,
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
            'user_id' => ['required', 'integer'],
            'voteable_id' => ['required', 'integer'],
            'voteable_type' => ['required', 'string', 'in:App\Models\Protocol,App\Models\Thread,App\Models\Comment'],
            'value' => ['required', 'integer', 'in:-1,1'],
        ];
    }
}
