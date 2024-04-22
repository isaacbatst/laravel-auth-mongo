<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    public function sendTest()
    {
        $to = config('app.zapi.phone_to_send');
        $response = $this->sendMessage('Hello, world!', $to);
        return response()->json([
            'response' => $response->json(),
            'status' => $response->status()
        ]);
    }

    public function receive(Request $request)
    {
        $body = $request->all();
        Log::info("Received message: \"{$body['text']['message']}\" from {$body['phone']}");
        if (!isset($body['text'])) {
            return response()->json([]);
        }

        $contact = Contact::firstOrCreate(
            ['phone' => $body['phone']],
        );

        $this->saveMessage($body['text']['message'], $body['phone'], $contact);
        return $this->reply($contact, $body['connectedPhone']);
    }

    private function reply(Contact $contact, string $connectedPhone) {
        $received = $contact->messages->last();
        if ($contact->wasRecentlyCreated) {
            $contact->onboardingStep = 'ask_name';
            $reply = "Olá, qual o seu nome?";
            $contact->save();
            $this->saveMessage($reply, $connectedPhone, $contact);
            $response = $this->sendMessage($reply, $contact->phone);
            return response()->json([
                'response' => $response->json(),
                'status' => $response->status()
            ]);
        }

        if ($contact->onboardingStep === 'ask_name') {
            $contact->onboardingStep = "finish";
            $contact->name = $received['text'];
            $contact->save();
            $reply = "Olá, {$contact->name}! Como posso te ajudar?";
            $this->saveMessage($reply, $connectedPhone, $contact);
            $response = $this->sendMessage($reply, $contact->phone);
            return response()->json([
                'response' => $response->json(),
                'status' => $response->status()
            ]);
        }

        $reply = "Olá {$contact->name}, como posso te ajudar?";
        $this->saveMessage($reply, $connectedPhone, $contact);
        $response = $this->sendMessage($reply, $contact->phone);
        return response()->json([
            'response' => $response->json(),
            'status' => $response->status()
        ]);
    }

    private function saveMessage($text, $from, $contact)
    {
        $contact->messages()->create([
            'text' => $text,
            'phone' => $from,
            'created_at' => now()
        ]);
    }

    private function sendMessage(string $text, string $to)
    {
        $baseUrl = config('app.zapi.url');
        $url = "{$baseUrl}/send-text";
        $clientToken = config('app.zapi.client_token');

        return Http::withHeader('Client-Token', $clientToken)
            ->post($url, [
                'phone' => $to,
                'message' => $text,
            ]);
    }
}
