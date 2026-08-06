<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UnassignResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'commandId' => ['required', 'uuid'],
            'baseRevision' => ['required', 'integer', 'min:0'],
            'type' => ['required', 'in:resident.unassign'],
        ];
    }
}
