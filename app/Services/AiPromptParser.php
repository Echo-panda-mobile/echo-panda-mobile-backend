<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiPromptParser
{
    /**
     * Use LLM to parse natural language into structured metadata.
     */
    public function parse(string $prompt): array
    {
        $systemPrompt = "You are a music recommendation assistant. Parse the user's prompt into a JSON object.
        Fields to extract:
        - genre: (string) e.g. 'Pop', 'Khmer Pop'
        - mood: (string) e.g. 'Sad', 'Energetic'
        - tags: (array of strings) e.g. ['Study', 'Workout']
        - similar_artist: (string) Name of artist mentioned
        - language: (string) e.g. 'Khmer', 'English'
        - energy: (float 0.0 to 1.0)
        - time_context: (string) e.g. 'Night', 'Morning'

        Only return the JSON. No conversational text.";

        try {
            // Integration with OpenAI/Gemini (Example using OpenAI structure)
            // In a real env, you would put your API key in .env
            $apiKey = config('services.openai.key');

            if (!$apiKey) {
                // Fallback / Mock for development if no API key
                return $this->mockParse($prompt);
            }

            $response = Http::withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'response_format' => ['type' => 'json_object']
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return json_decode($data['choices'][0]['message']['content'], true);
            }

            Log::error("AI Parsing failed", ['response' => $response->body()]);
            return $this->mockParse($prompt);

        } catch (\Exception $e) {
            Log::error("AI Parsing exception: " . $e->getMessage());
            return $this->mockParse($prompt);
        }
    }

    /**
     * Simple keyword-based fallback if AI is unavailable.
     */
    private function mockParse(string $prompt): array
    {
        $prompt = strtolower($prompt);
        $result = [
            'genre' => null,
            'mood' => null,
            'tags' => [],
            'similar_artist' => null,
            'language' => null
        ];

        if (str_contains($prompt, 'khmer')) $result['language'] = 'Khmer';
        if (str_contains($prompt, 'study')) $result['tags'][] = 'Study';
        if (str_contains($prompt, 'workout')) $result['tags'][] = 'Workout';
        if (str_contains($prompt, 'sad')) $result['mood'] = 'Sad';
        if (str_contains($prompt, 'jennie')) $result['similar_artist'] = 'Jennie';

        return $result;
    }
}
