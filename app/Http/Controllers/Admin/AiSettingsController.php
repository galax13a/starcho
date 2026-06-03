<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use Illuminate\Http\Request;

class AiSettingsController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'provider' => ['required', 'in:' . implode(',', array_keys(AiSetting::PROVIDERS))],
            'openai_api_key' => ['nullable', 'string', 'max:1000'],
            'deepseek_api_key' => ['nullable', 'string', 'max:1000'],
            'anthropic_api_key' => ['nullable', 'string', 'max:1200'],
            'openrouter_api_key' => ['nullable', 'string', 'max:1200'],
            'default_model' => ['required', 'string', 'max:120'],
            'ai_models' => ['nullable', 'array'],
            'ai_models.*' => ['nullable', 'array'],
            'ai_models.*.*.id' => ['nullable', 'string', 'max:180'],
            'ai_models.*.*.active' => ['nullable', 'boolean'],
        ]);

        $settings = AiSetting::singleton();

        $payload = [
            'enabled' => $request->boolean('enabled'),
            'provider' => $data['provider'],
            'default_model' => trim($data['default_model']),
            'model_settings' => $settings->normalizeModelSettings($request->input('ai_models', [])),
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

        if (filled($data['openrouter_api_key'] ?? null)) {
            $payload['openrouter_api_key'] = trim($data['openrouter_api_key']);
        }

        $settings->update($payload);

        return redirect()
            ->route('admin.site.index', ['tab' => 'ai'])
            ->with('success', 'Configuración AI guardada correctamente.');
    }
}
