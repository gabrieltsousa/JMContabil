<?php

declare(strict_types=1);

namespace App\Http\Requests\Charge;

use Illuminate\Foundation\Http\FormRequest;

final class SendNotificationRequest extends FormRequest
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
            'charge_id' => ['nullable', 'integer', 'exists:charges,id', 'required_without:customer_id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id', 'required_without:charge_id'],
            'force' => ['sometimes', 'boolean'],
            'async' => ['sometimes', 'boolean'],
        ];
    }
}
