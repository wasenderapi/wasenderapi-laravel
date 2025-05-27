<?php

namespace WasenderApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header(config('wasenderapi.webhook_signature_header'));
        $secret = config('wasenderapi.webhook_secret');

        if (!$signature || !$secret || $signature !== $secret) {
            return response('Invalid signature', 400);
        }

        $payload = $request->json()->all();
        if (!isset($payload['event'])) {
            return response('Invalid payload', 400);
        }

        $eventMap = [
            'chats.upsert' => \WasenderApi\Events\ChatsUpserted::class,
            'chats.update' => \WasenderApi\Events\ChatsUpdated::class,
            'chats.delete' => \WasenderApi\Events\ChatsDeleted::class,
            'groups.upsert' => \WasenderApi\Events\GroupsUpserted::class,
            'groups.update' => \WasenderApi\Events\GroupsUpdated::class,
            'group-participants.update' => \WasenderApi\Events\GroupParticipantsUpdated::class,
            'contacts.upsert' => \WasenderApi\Events\ContactsUpserted::class,
            'contacts.update' => \WasenderApi\Events\ContactsUpdated::class,
            'messages.upsert' => \WasenderApi\Events\MessagesUpserted::class,
            'messages.update' => \WasenderApi\Events\MessagesUpdated::class,
            'messages.delete' => \WasenderApi\Events\MessagesDeleted::class,
            'messages.reaction' => \WasenderApi\Events\MessagesReaction::class,
            'message-receipt.update' => \WasenderApi\Events\MessageReceiptUpdated::class,
            'message.sent' => \WasenderApi\Events\MessageSent::class,
            'session.status' => \WasenderApi\Events\SessionStatus::class,
            'qrcode.updated' => \WasenderApi\Events\QrCodeUpdated::class,
        ];
        $eventType = $payload['event'];
        $eventClass = $eventMap[$eventType] ?? \WasenderApi\Events\WasenderWebhookEvent::class;
        Event::dispatch(new $eventClass($payload));

        return response('OK', 200);
    }
} 