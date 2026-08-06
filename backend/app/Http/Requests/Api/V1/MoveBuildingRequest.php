<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class MoveBuildingRequest extends FormRequest
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
            'type' => ['required', 'in:building.move'],
            'payload' => ['required', 'array'],
            'payload.x' => ['required', 'integer', 'min:0', 'max:19'],
            'payload.y' => ['required', 'integer', 'min:0', 'max:19'],
            'payload.rotation' => ['required', 'in:0,90,180,270'],
        ];
    }
}
