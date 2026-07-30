<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Domain\Settings\Enums\WhatsAppProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSettingsRequest extends FormRequest
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
            'company_name' => ['sometimes', 'string', 'max:255'],
            'default_message' => ['sometimes', 'string'],
            'whatsapp_provider' => ['sometimes', Rule::enum(WhatsAppProvider::class)],
            'timezone' => ['sometimes', 'string', 'timezone'],
        ];
    }
}
