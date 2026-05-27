<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiSettingsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'provider' => ['required', 'in:openai,deepseek,anthropic'],
            'openai_api_key' => ['nullable', 'string', 'max:1000'],
            'deepseek_api_key' => ['nullable', 'string', 'max:1000'],
            'anthropic_api_key' => ['nullable', 'string', 'max:1200'],
            'default_model' => ['required', 'string', 'max:120'],
        ]);

        $settings = AiSetting::singleton();

        $payload = [
            'enabled' => $request->boolean('enabled'),
            'provider' => $data['provider'],
            'default_model' => trim($data['default_model']),
        ];

        if (filled($data['openai_api_key'] ?? null)) {
            $payload['openai_api_key'] = trim($data['openai_api_key']);
        }

        if (filled($data['deepseek_api_key'] ?? null)) {
            $payload['deepseek_api_key'] = trim($data['deepseek_api_key']);
        }

        if (filled($data['anthropic_api_key'] ?? null)) {
            $payload['anthropic_api_key'] = trim($data['anthropic_api_key']);
        }

        $settings->update($payload);

        return redirect()
            ->route('admin.site.index', ['tab' => 'ai'])
            ->with('success', 'Configuración AI guardada correctamente.');
    }
}
