<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
        $contact = Contact::firstOrCreate(
            ['phone' => $body['phone']],
        );

        $received = $this->toDomain($body);
        
        if(!isset($received)) {
            return response()->json([]);
        }
        $contact->messages()->save($received);
        return $this->reply($contact, $body['connectedPhone']);
    }

    private function reply(Contact $contact, string $connectedPhone) {
        $received = $contact->messages->last();
        if ($contact->wasRecentlyCreated) {
            $contact->step = 'ask_name';
            $contact->save();
            $reply = "Olá, qual o seu nome?";
            $this->saveTextMessage($reply, $connectedPhone, $contact);
            $response = $this->sendMessage($reply, $contact->phone);
            return response()->json([
                'response' => $response->json(),
                'status' => $response->status()
            ]);
        }

        if ($contact->step === 'ask_name') {
            $contact->name = $received['text'];
            $contact->step = "menu";
            $contact->save();

            $reply = "Olá, {$contact->name}! O que você deseja?\n- 1 - Informar um novo foco de zoonoze\n- 2 - Ver mapa com locais de foco de zoonoses";
            $this->saveTextMessage($reply, $connectedPhone, $contact);
            $response = $this->sendMessage($reply, $contact->phone);
            return response()->json([
                'response' => $response->json(),
                'status' => $response->status()
            ]);
        }

        if($contact->step === 'menu') {
            if($received['text'] === '1') {
                $contact->step = 'report_focus';
                $contact->save();
                $reply = "Informe o foco de zoonoze";
                $this->saveTextMessage($reply, $connectedPhone, $contact);
                $response = $this->sendMessage($reply, $contact->phone);
                return response()->json([
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
            }

            if($received['text'] === '2') {
                $contact->step = 'show_map';
                $contact->save();
                $reply = "Aqui está o mapa com os locais de foco de zoonoses";
                $this->saveTextMessage($reply, $connectedPhone, $contact);
                $response = $this->sendMessage($reply, $contact->phone);
                return response()->json([
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
            }

            $reply = "Opção inválida. O que você deseja?\n- 1 - Informar um novo foco de zoonoze\n- 2 - Ver mapa com locais de foco de zoonoses";
            $this->saveTextMessage($reply, $connectedPhone, $contact);
            $response = $this->sendMessage($reply, $contact->phone);
            return response()->json([
                'response' => $response->json(),
                'status' => $response->status()
            ]);
        }

        if($contact->step === 'report_focus') {
            if(!isset($received['location'])) {
                $reply = "Por favor, envie a localização do foco de zoonoze";
                $this->saveTextMessage($reply, $connectedPhone, $contact);
                $response = $this->sendMessage($reply, $contact->phone);
                return response()->json([
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
            }

            $contact->step = 'menu';
            $contact->save();
            $reply = "Obrigado por informar o foco de zoonoze!";
            $this->saveTextMessage($reply, $connectedPhone, $contact);
            $response = $this->sendMessage($reply, $contact->phone);
            return response()->json([
                'response' => $response->json(),
                'status' => $response->status()
            ]);
        }

        if($contact->step === 'show_map') {
            $contact->step = 'menu';
            $contact->save();
            $reply = "Obrigado por visualizar o mapa!";
            $this->saveTextMessage($reply, $connectedPhone, $contact);
            $response = $this->sendMessage($reply, $contact->phone);
            return response()->json([
                'response' => $response->json(),
                'status' => $response->status()
            ]);
        }
        
        $contact->step = "menu";        
        $contact->save();

        $reply = "Olá, {$contact->name}! O que você deseja?\n- 1 - Informar um novo foco de zoonoze\n- 2 - Ver mapa com locais de foco de zoonoses";
        $this->saveTextMessage($reply, $connectedPhone, $contact);
        $response = $this->sendMessage($reply, $contact->phone);
        return response()->json([
            'response' => $response->json(),
            'status' => $response->status()
        ]);
    }

    private function toDomain($body): ?Message {
        if(isset($body['location'])) {
            $message = new Message();
            $message->phone = $body['phone'];
            $message->location = $body['location'];
            $message->created_at = now();
            return $message;
        }

        if(isset($body['text'])) {
            $message = new Message();
            $message->phone = $body['phone'];
            $message->text = $body['text']['message'];
            $message->created_at = now();
            return $message;
        }

        return null;
    }

    private function saveTextMessage($text, $from, $contact)
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
