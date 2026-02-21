<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoteRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'voteable_id' => ['required', 'integer'],
            'voteable_type' => ['required', 'string', 'in:App\Models\Protocol,App\Models\Thread,App\Models\Comment'],
            'value' => ['required', 'integer', 'in:-1,1'],
        ];
    }
}
