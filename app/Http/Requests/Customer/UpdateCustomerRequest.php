<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use App\Domain\Customer\Enums\CustomerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateCustomerRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'pix_key' => ['sometimes', 'string', 'max:77'],
            'monthly_value' => ['sometimes', 'numeric', 'min:0.01'],
            'due_day' => ['sometimes', 'integer', 'min:1', 'max:28'],
            'status' => ['sometimes', Rule::enum(CustomerStatus::class)],
        ];
    }
}
