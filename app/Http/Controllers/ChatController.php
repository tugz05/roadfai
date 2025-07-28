<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    // Render the Inertia Vue page
    public function index()
    {
        return Inertia::render('Chatbot');
    }

    // Handle incoming messages and forward to Ollama
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $response = Http::post(config('services.ollama.url') . '/api/generate', [
            'model'  => config('services.ollama.model'),
            'prompt' => $request->message,
            'stream' => false,
        ]);

        if ($response->ok()) {
            $payload = $response->json();
            return response()->json([
                'reply' => $payload['response'] ?? 'No response from AI.',
            ]);
        }

        return response()->json([
            'error' => 'Ollama API returned status ' . $response->status(),
        ], 500);
    }
}
