<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    protected string $baseUrl;
    protected string $model;

public function __construct()
{
    $this->baseUrl = env('OLLAMA_API_URL', 'http://localhost:11434/api/generate');
    $this->model   = env('OLLAMA_MODEL', 'phi3:mini');
}

    /**
     * Send prompt to Ollama and return the generated text.
     *
     * @param string $prompt
     * @return string
     * @throws \Exception
     */
    public function generate(string $prompt): string
    {
        $response = Http::post($this->baseUrl, [
            'model'  => $this->model,
            'prompt' => $prompt,
            'stream' => false,
        ]);

        if ($response->successful()) {
            return $response->json('response') ?? '';
        }

        throw new \Exception('Ollama error: ' . $response->body());
    }
}
