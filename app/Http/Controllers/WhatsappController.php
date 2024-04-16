<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    public function receive(Request $request)
    {
        $body = $request->all();

        // Log::info('Received message from Z-API: ' . now());
        // Log::info($body);

        if (!isset($body['text'])) {
            // return empty with 200
            return response()->json([]);
        }

        $contact = Contact::firstOrCreate(
            ['phone' => $body['phone']],
        );

        // attach message to contact
        $contact->messages()->create([
            'text' => $body['text'],
            'phone' => $body['phone'],
            'created_at' => now()
        ]);

        if($contact->wasRecentlyCreated) {
            $response = $this->sendMessage('Olá, me informe o seu nome, por favor.');
            return response()->json([
                'response' => $response->json(),
                'status' => $response->status()
            ]);
        }

        $response = $this->sendMessage("Olá, {$contact->name}. Já temos o seu contato!");
        return response()->json([
            'response' => $response->json(),
            'status' => $response->status()
        ]);
    }

    private function sendMessage($text) {
        $baseUrl = config('app.zapi.url');
        $url = "{$baseUrl}/send-text";
        $clientToken = config('app.zapi.client_token');
        $phone = config('app.zapi.phone_to_send');

        return Http::withHeader('Client-Token', $clientToken)
            ->post($url, [
                'phone' => $phone,
                'message' => 'Hello, world!'
            ]);

    }
}
