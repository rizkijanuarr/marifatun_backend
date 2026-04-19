<?php

namespace App\Services\V1;

use App\Enums\ContentTypeEnum;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenRouterService
{
    public function generateCopywriting(array $input): string
    {
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
