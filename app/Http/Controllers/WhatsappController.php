<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Location;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    static $menu = "- 1 - Informar um novo foco de zoonoze\n- 2 - Ver mapa com locais de foco de zoonoses";

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

        if($contact->step === 'report_focus' && isset($received->location)) {
            Location::create([
                'lat' => $received['location']['lat'],
                'lng' => $received['location']['lng'],
                'created_at' => now()
            ]);
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

            $reply = "Olá, {$contact->name}! O que você deseja?\n{$this::$menu}";
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
                $contact->step = 'menu';
                $contact->save();
                $link = $this->getMapLink();
                $reply = $this->getMapMessage($link);
                $this->saveTextMessage($reply, $connectedPhone, $contact);
                $response = $this->sendMapLink($contact->phone, $reply, $link);
                return response()->json([
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
            }

            $reply = "Opção inválida. O que você deseja?\n{$this::$menu}";
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
            $reply = "Obrigado por informar o foco de zoonoze!\n{$this::$menu}";
            $this->saveTextMessage($reply, $connectedPhone, $contact);
            $response = $this->sendMessage($reply, $contact->phone);
            return response()->json([
                'response' => $response->json(),
                'status' => $response->status()
            ]);
        }

        $contact->step = "menu";        
        $contact->save();

        $reply = "Olá, {$contact->name}! O que você deseja?\n{$this::$menu}";
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
            $message->location = [
                'lat' => $body['location']['latitude'],
                'lng' => $body['location']['longitude']
            ];
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

    private function getMapLink() {
        return env('APP_URL') . '/mapa';
    }

    private function getMapMessage($link)
    {
        return "Aqui está o mapa com os locais de foco de zoonoses: {$link}";
    }

    private function sendMapLink(string $to, string $message, string $link)
    {
        $title = 'Mapa - Dengue Alert';
        $description = 'Clique no link para ver o mapa com os locais de foco de zoonoses';
        return $this->sendLink(
            $link,
            $message,
            $to, 
            $title, 
            $description
        );
    }

    private function sendLink(
        string $link, 
        string $message, 
        string $to, 
        string $title, 
        string $description
    )
    {
        $baseUrl = config('app.zapi.url');
        $url = "{$baseUrl}/send-link";
        $clientToken = config('app.zapi.client_token');

        return Http::withHeader('Client-Token', $clientToken)
            ->post($url, [
                'phone' => $to,
                'message' => $message,
                'linkUrl' => $link,
                'title' => $title,
                'linkDescription' => $description,
            ]);
    }
}
