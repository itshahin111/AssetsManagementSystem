<?php

namespace App\Http\Requests;

class UpdateSortOrderRequest extends ApiFormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
