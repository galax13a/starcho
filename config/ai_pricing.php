<?php

/*
|--------------------------------------------------------------------------
| AI pricing
|--------------------------------------------------------------------------
| Real provider costs (approximate, in USD) used to compute how much each
| generation costs you, and the price billed to the user.
|
|   user_price = provider_cost * markup
|
| `markup = 1.8` => the user pays the real cost plus an 80% margin.
| Adjust the numbers below to match the rates of your provider plan.
*/

return [

    // 1.8 = cost + 80% margin
    'markup' => 1.8,

    // Text models: USD per 1,000,000 tokens (blended input+output estimate).
    // total_tokens from post_ai_generations is multiplied by this.
    'text' => [
        'default' => 0.60,
        'models' => [
            'gpt-5.4-nano'                  => 0.20,
            'gpt-5.4-mini'                  => 0.70,
            'gpt-5.4'                       => 5.00,
            'gpt-5.4-pro'                   => 15.00,
            'deepseek-chat'                 => 0.50,
            'deepseek-reasoner'             => 1.20,
            'claude-haiku-4-5-20251001'     => 1.00,
            'claude-sonnet-4-6'             => 6.00,
            'openai/gpt-4o-mini'            => 0.45,
            'openai/gpt-4o'                 => 7.50,
            'anthropic/claude-sonnet-4.6'   => 6.00,
            'anthropic/claude-haiku-4.5'    => 1.00,
            'google/gemini-2.0-flash-001'   => 0.30,
        ],
    ],

    // Image models: USD per generated image.
    'image' => [
        'default' => 0.04,
        'models' => [
            'gpt-image-1' => 0.04,
            'dall-e-3'    => 0.04,
            // fal.ai
            'fal-ai/flux/schnell'                 => 0.003,
            'fal-ai/flux/dev'                     => 0.025,
            'fal-ai/flux-pro/v1.1'                => 0.04,
            'fal-ai/recraft-v3'                   => 0.04,
            'fal-ai/stable-diffusion-v35-large'   => 0.035,
            // Replicate
            'black-forest-labs/flux-schnell'   => 0.003,
            'black-forest-labs/flux-dev'       => 0.025,
            'black-forest-labs/flux-1.1-pro'   => 0.04,
            'stability-ai/sdxl'                => 0.01,
            'ideogram-ai/ideogram-v2'          => 0.08,
        ],
    ],

    // Video models: USD per generated video (approximate per ~5s clip).
    'video' => [
        'default' => 0.50,
        'models' => [
            'fal-ai/kling-video/v1/standard/text-to-video'    => 0.35,
            'fal-ai/kling-video/v1.6/standard/text-to-video'  => 0.45,
            'fal-ai/veo2'                                      => 2.50,
            'fal-ai/minimax/video-01'                          => 0.50,
            'fal-ai/luma-dream-machine'                        => 0.50,
            // Replicate
            'kwaivgi/kling-v1.6-standard'                      => 0.45,
            'minimax/video-01'                                 => 0.50,
            'wan-video/wan-2.2-t2v-fast'                       => 0.20,
            'tencent/hunyuan-video'                            => 0.60,
            'luma/ray'                                         => 0.55,
        ],
    ],
];
