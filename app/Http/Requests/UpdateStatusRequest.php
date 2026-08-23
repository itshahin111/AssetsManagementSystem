<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateStatusRequest extends ApiFormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
