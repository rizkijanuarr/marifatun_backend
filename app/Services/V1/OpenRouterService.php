<?php

namespace App\Services\V1;

use App\Enums\ContentTypeEnum;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenRouterService
{
    public function generateCopywriting(array $input): string
    {
        if (($input['content_type'] ?? '') === ContentTypeEnum::VIDEO_SCRIPT->value) {
            throw new RuntimeException('Gunakan generateVideoScript() untuk tipe video_script.');
        }

        $apiKey = config('services.openrouter.key');
        if (empty($apiKey)) {
            throw new RuntimeException('OPENROUTER_API_KEY belum dikonfigurasi.');
        }

        $prompt = $this->buildPrompt($input);

        $response = Http::withToken($apiKey)
            ->withHeaders([
                'HTTP-Referer' => (string) config('services.openrouter.http_referer'),
                'X-Title' => (string) config('services.openrouter.app_title'),
                'Content-Type' => 'application/json',
            ])
            ->timeout((int) config('services.openrouter.timeout', 60))
            ->post(rtrim((string) config('services.openrouter.base_url'), '/').'/chat/completions', [
                'model' => (string) config('services.openrouter.model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Kamu adalah copywriter profesional Marifatun yang ahli membuat konten pemasaran singkat, menarik, dan sesuai platform target. Jawab langsung dalam bahasa Indonesia tanpa preambule.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.8,
                'max_tokens' => 1200,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gagal memanggil OpenRouter: HTTP '.$response->status().' '.$response->body());
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? null;

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Respons LLM kosong atau tidak valid.');
        }

        return trim($text);
    }

    /**
     * Hasil: string JSON valid {"scenes":[...]} — disimpan di kolom `result`.
     */
    public function generateVideoScript(array $input): string
    {
        $apiKey = config('services.openrouter.key');
        if (empty($apiKey)) {
            throw new RuntimeException('OPENROUTER_API_KEY belum dikonfigurasi.');
        }

        $userPrompt = $this->buildVideoScriptUserPrompt($input);

        $response = Http::withToken($apiKey)
            ->withHeaders([
                'HTTP-Referer' => (string) config('services.openrouter.http_referer'),
                'X-Title' => (string) config('services.openrouter.app_title'),
                'Content-Type' => 'application/json',
            ])
            ->timeout((int) config('services.openrouter.timeout', 120))
            ->post(rtrim((string) config('services.openrouter.base_url'), '/').'/chat/completions', [
                'model' => (string) config('services.openrouter.model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional video script writer and marketing strategist. Return ONLY valid JSON, no markdown fences, no commentary — bahasa Indonesia untuk teks narasi dan on-screen.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],
                'temperature' => 0.55,
                'max_tokens' => 4096,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gagal memanggil OpenRouter: HTTP '.$response->status().' '.$response->body());
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? null;

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Respons LLM kosong atau tidak valid.');
        }

        $raw = $this->stripJsonFence(trim($text));
        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Respons LLM bukan JSON yang valid.');
        }

        $this->assertValidVideoScriptPayload($decoded);

        $normalized = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function buildVideoScriptUserPrompt(array $input): string
    {
        $topic = $input['topic'] ?? '';
        $audience = $input['target_audience'] ?? 'umum';
        $platform = $input['video_platform'] ?? 'instagram';
        $platformLabel = match ($platform) {
            'tiktok' => 'TikTok',
            'instagram' => 'Instagram',
            'linkedin' => 'LinkedIn',
            default => (string) $platform,
        };
        $keyMessage = $input['video_key_message'] ?? '';
        $cta = $input['video_cta'] ?? '';
        $tone = $input['tone'] ?? 'professional';

        return <<<PROMPT
You are a professional video script writer and marketing strategist.

Create a short-form marketing video script with the following requirements:

VIDEO OBJECTIVE:
Generate a promotional video that is engaging, informative, and optimized for social media.

VIDEO SPECIFICATIONS:
- Duration: 30-45 seconds
- Format: Vertical video (9:16)
- Language: Bahasa Indonesia
- Tone: Persuasive and professional (nada brief: {$tone})
- Style: Modern, clean, minimal

INPUT DATA:
Topic: {$topic}
Target Audience: {$audience}
Platform: {$platformLabel} (TikTok / Instagram / LinkedIn)
Key Message: {$keyMessage}
Call To Action: {$cta}

OUTPUT FORMAT:
Return structured JSON with scenes.

Each scene must contain:
- scene_number (number)
- duration_seconds (number)
- visual_description (string)
- on_screen_text (string)
- voice_over (string)
- background_music (string)
- transition (string)

VIDEO STRUCTURE:
Scene 1: Hook (0-3s)
Scene 2: Problem (3-10s)
Scene 3: Solution (10-20s)
Scene 4: Benefits (20-30s)
Scene 5: CTA (30-40s)

IMPORTANT:
- Keep sentences short
- Use engaging hooks
- Optimize for mobile viewers
- Use strong call to action
- Avoid long paragraphs
- Make visuals easy to generate

Return ONLY JSON.

Example shape:
{"scenes":[{"scene_number":1,"duration_seconds":3,"visual_description":"...","on_screen_text":"...","voice_over":"...","background_music":"...","transition":"fade"}]}
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $decoded
     */
    private function assertValidVideoScriptPayload(array $decoded): void
    {
        if (! isset($decoded['scenes']) || ! is_array($decoded['scenes']) || $decoded['scenes'] === []) {
            throw new RuntimeException('JSON skrip video harus memiliki "scenes" berisi array tidak kosong.');
        }

        $keys = ['scene_number', 'duration_seconds', 'visual_description', 'on_screen_text', 'voice_over', 'background_music', 'transition'];

        foreach ($decoded['scenes'] as $idx => $scene) {
            if (! is_array($scene)) {
                throw new RuntimeException('Setiap adegan harus berupa objek JSON (indeks '.$idx.').');
            }
            foreach ($keys as $k) {
                if (! array_key_exists($k, $scene)) {
                    throw new RuntimeException('Adegan '.($idx + 1).' tidak memiliki field "'.$k.'".');
                }
            }
        }
    }

    private function stripJsonFence(string $text): string
    {
        $t = trim($text);
        if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/iu', $t, $m)) {
            return trim($m[1]);
        }

        return $t;
    }

    private function buildPrompt(array $input): string
    {
        $type = $input['content_type'] ?? ContentTypeEnum::LINKEDIN->value;
        $typeLabel = ContentTypeEnum::tryFrom($type)?->label() ?? $type;
        $topic = $input['topic'] ?? '';
        $keywords = $input['keywords'] ?? '';
        $audience = $input['target_audience'] ?? 'general audience';
        $tone = $input['tone'] ?? 'casual';

        $format = match ($type) {
            ContentTypeEnum::LINKEDIN->value => 'Tulis post LinkedIn 120-180 kata, gunakan hook kuat di baris pertama, 1-2 emoji bila relevan, tutup dengan call to action dan 3-5 hashtag relevan.',
            ContentTypeEnum::X->value => 'Tulis post X (Twitter) maksimal 280 karakter, padat dan menarik, sertakan 1-2 hashtag relevan.',
            ContentTypeEnum::THREAD->value => 'Tulis thread Threads 3-5 paragraf pendek, bernada conversational, gunakan line break, tanpa nomor, tutup dengan ajakan engagement.',
            ContentTypeEnum::FACEBOOK->value => 'Tulis post Facebook 100-160 kata, storytelling singkat, 1-2 emoji, dan CTA di akhir.',
            ContentTypeEnum::EMAIL_MARKETING->value => 'Tulis email marketing dengan struktur: Subject line, Preheader, Body 150-220 kata, dan CTA Button text. Format dengan label jelas seperti "Subject:", "Preheader:", "Body:", "CTA:".',
            default => 'Tulis konten copywriting yang engaging dan sesuai tujuan.',
        };

        return <<<PROMPT
Buat konten {$typeLabel}.

Detail:
- Topik: {$topic}
- Kata kunci: {$keywords}
- Target audiens: {$audience}
- Tone: {$tone}

Instruksi format: {$format}
PROMPT;
    }
}
