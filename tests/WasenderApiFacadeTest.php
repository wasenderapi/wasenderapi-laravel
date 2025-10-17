<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use WasenderApi\Facades\WasenderApi;
use WasenderApi\Exceptions\WasenderApiException;
use WasenderApi\Data\SendTextMessageData;
use WasenderApi\Data\SendImageMessageData;
use WasenderApi\Data\SendVideoMessageData;
use WasenderApi\Data\SendDocumentMessageData;
use WasenderApi\Data\SendAudioMessageData;
use WasenderApi\Data\SendStickerMessageData;
use WasenderApi\Data\SendContactMessageData;
use WasenderApi\Data\SendLocationMessageData;
use WasenderApi\Events\MessagesUpserted;
use WasenderApi\Events\CallReceived;
use WasenderApi\Events\WasenderWebhookEvent;

uses(\Orchestra\Testbench\TestCase::class);

beforeEach(function () {
    $this->app->register(\WasenderApi\WasenderApiServiceProvider::class);
    Config::set('wasenderapi.api_key', 'testkey');
    Config::set('wasenderapi.personal_access_token', 'testpat');
});

test('sendText via Facade returns success and message', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Message sent'], 200),
    ]);
    $result = WasenderApi::sendText('123', 'hello');
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Message sent');
});

test('sendText via Facade throws exception on error', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-message' => Http::response(['error' => 'fail'], 400),
    ]);
    WasenderApi::sendText(new SendTextMessageData('123', 'fail'));
})->throws(WasenderApiException::class);

test('sendImage via Facade returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Image sent'], 200),
    ]);
    $result = WasenderApi::sendImage(new SendImageMessageData('123', 'https://img', 'caption'));
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Image sent');
});

test('sendVideo via Facade returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Video sent'], 200),
    ]);
    $result = WasenderApi::sendVideo(new SendVideoMessageData('123', 'https://vid', 'caption'));
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Video sent');
});

test('sendDocument via Facade returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Document sent'], 200),
    ]);
    $result = WasenderApi::sendDocument(new SendDocumentMessageData('123', 'https://doc', 'caption'));
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Document sent');
});

test('sendAudio via Facade returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Audio sent'], 200),
    ]);
    $result = WasenderApi::sendAudio(new SendAudioMessageData('123', 'https://audio'));
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Audio sent');
});

test('sendSticker via Facade returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Sticker sent'], 200),
    ]);
    $result = WasenderApi::sendSticker(new SendStickerMessageData('123', 'https://sticker'));
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Sticker sent');
});

test('sendContact via Facade returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Contact sent'], 200),
    ]);
    $result = WasenderApi::sendContact(new SendContactMessageData('123', 'John Doe', '+123456789'));
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Contact sent');
});

test('sendMessageWithMentions via Facade returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Mention sent'], 200),
    ]);
    $result = WasenderApi::sendMessageWithMentions('123@g.us', 'Hello @user', ['123@s.whatsapp.net']);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Mention sent');
});

test('sendQuotedMessage via Facade returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Quoted sent'], 200),
    ]);
    $result = WasenderApi::sendQuotedMessage('123', 42, 'Hello', ['imageUrl' => 'https://img']);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Quoted sent');
});

test('sendQuotedMessage throws on invalid reply id', function () {
    WasenderApi::sendQuotedMessage('123', 0, 'Hello');
})->throws(InvalidArgumentException::class);

test('sendLocation via Facade returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-message' => Http::response(['success' => true, 'message' => 'Location sent'], 200),
    ]);
    $result = WasenderApi::sendLocation(new SendLocationMessageData('123', 1.23, 4.56, 'Place', 'Address'));
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Location sent');
});

test('getContacts returns contacts', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/contacts' => Http::response(['success' => true, 'contacts' => []], 200),
    ]);
    $result = WasenderApi::getContacts();
    expect($result['success'])->toBeTrue();
    expect($result['contacts'])->toBeArray();
});

test('getContactInfo returns info', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/contacts/123' => Http::response(['success' => true, 'info' => []], 200),
    ]);
    $result = WasenderApi::getContactInfo('123');
    expect($result['success'])->toBeTrue();
    expect($result['info'])->toBeArray();
});

test('getContactProfilePicture returns url', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/contacts/123/profile-picture' => Http::response(['success' => true, 'url' => 'https://img'], 200),
    ]);
    $result = WasenderApi::getContactProfilePicture('123');
    expect($result['success'])->toBeTrue();
    expect($result['url'])->toBe('https://img');
});

test('blockContact returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/contacts/123/block' => Http::response(['success' => true, 'message' => 'Blocked'], 200),
    ]);
    $result = WasenderApi::blockContact('123');
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Blocked');
});

test('unblockContact returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/contacts/123/unblock' => Http::response(['success' => true, 'message' => 'Unblocked'], 200),
    ]);
    $result = WasenderApi::unblockContact('123');
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Unblocked');
});

test('checkIfOnWhatsapp returns status', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/on-whatsapp/%2B1234567890' => Http::response(['success' => true, 'exists' => true], 200),
    ]);
    $result = WasenderApi::checkIfOnWhatsapp('+1234567890');
    expect($result['success'])->toBeTrue();
    expect($result['exists'])->toBeTrue();
});

test('createGroup returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups' => Http::response(['success' => true, 'id' => 'abc@g.us'], 200),
    ]);
    $result = WasenderApi::createGroup('My Group', ['u1@s.whatsapp.net']);
    expect($result['success'])->toBeTrue();
    expect($result['id'])->toBe('abc@g.us');
});

test('getGroups returns groups', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups' => Http::response(['success' => true, 'groups' => []], 200),
    ]);
    $result = WasenderApi::getGroups();
    expect($result['success'])->toBeTrue();
    expect($result['groups'])->toBeArray();
});

test('getGroupMetadata returns metadata', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups/abc/metadata' => Http::response(['success' => true, 'metadata' => []], 200),
    ]);
    $result = WasenderApi::getGroupMetadata('abc');
    expect($result['success'])->toBeTrue();
    expect($result['metadata'])->toBeArray();
});

test('getGroupParticipants returns participants', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups/abc/participants' => Http::response(['success' => true, 'participants' => []], 200),
    ]);
    $result = WasenderApi::getGroupParticipants('abc');
    expect($result['success'])->toBeTrue();
    expect($result['participants'])->toBeArray();
});

test('addGroupParticipants returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups/abc/participants/add' => Http::response(['success' => true, 'message' => 'Added'], 200),
    ]);
    $result = WasenderApi::addGroupParticipants('abc', ['u1', 'u2']);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Added');
});

test('removeGroupParticipants returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups/abc/participants/remove' => Http::response(['success' => true, 'message' => 'Removed'], 200),
    ]);
    $result = WasenderApi::removeGroupParticipants('abc', ['u1', 'u2']);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Removed');
});

test('updateGroupParticipants returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups/abc/participants/update' => Http::response(['success' => true, 'message' => 'Updated'], 200),
    ]);
    $result = WasenderApi::updateGroupParticipants('abc', 'promote', ['u1']);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Updated');
});

test('updateGroupParticipants throws on invalid action', function () {
    WasenderApi::updateGroupParticipants('abc', 'invalid', ['u1']);
})->throws(InvalidArgumentException::class);

test('updateGroupSettings returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups/abc/settings' => Http::response(['success' => true, 'message' => 'Updated'], 200),
    ]);
    $result = WasenderApi::updateGroupSettings('abc', ['setting' => 'value']);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Updated');
});

test('getAllWhatsAppSessions returns sessions', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/whatsapp-sessions' => Http::response(['success' => true, 'sessions' => []], 200),
    ]);
    $result = WasenderApi::getAllWhatsAppSessions();
    expect($result['success'])->toBeTrue();
    expect($result['sessions'])->toBeArray();
});

test('createWhatsAppSession returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/whatsapp-sessions' => Http::response(['success' => true, 'message' => 'Created'], 200),
    ]);
    $result = WasenderApi::createWhatsAppSession(['name' => 'test']);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Created');
});

test('getWhatsAppSessionDetails returns details', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/whatsapp-sessions/1' => Http::response(['success' => true, 'details' => []], 200),
    ]);
    $result = WasenderApi::getWhatsAppSessionDetails(1);
    expect($result['success'])->toBeTrue();
    expect($result['details'])->toBeArray();
});

test('updateWhatsAppSession returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/whatsapp-sessions/1' => Http::response(['success' => true, 'message' => 'Updated'], 200),
    ]);
    $result = WasenderApi::updateWhatsAppSession(1, ['name' => 'new']);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Updated');
});

test('deleteWhatsAppSession returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/whatsapp-sessions/1' => Http::response(['success' => true, 'message' => 'Deleted'], 200),
    ]);
    $result = WasenderApi::deleteWhatsAppSession(1);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Deleted');
});

test('connectWhatsAppSession returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/whatsapp-sessions/1/connect' => Http::response(['success' => true, 'message' => 'Connected'], 200),
    ]);
    $result = WasenderApi::connectWhatsAppSession(1);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Connected');
});

test('getWhatsAppSessionQrCode returns qr', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/whatsapp-sessions/1/qr-code' => Http::response(['success' => true, 'qr' => 'qrcode'], 200),
    ]);
    $result = WasenderApi::getWhatsAppSessionQrCode(1);
    expect($result['success'])->toBeTrue();
    expect($result['qr'])->toBe('qrcode');
});

test('disconnectWhatsAppSession returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/whatsapp-sessions/1/disconnect' => Http::response(['success' => true, 'message' => 'Disconnected'], 200),
    ]);
    $result = WasenderApi::disconnectWhatsAppSession(1);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Disconnected');
});

test('getGroupInviteInfo returns info', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups/invite/ABC123' => Http::response(['success' => true, 'info' => []], 200),
    ]);
    $result = WasenderApi::getGroupInviteInfo('ABC123');
    expect($result['success'])->toBeTrue();
    expect($result['info'])->toBeArray();
});

test('leaveGroup returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups/abc/leave' => Http::response(['success' => true, 'message' => 'Left'], 200),
    ]);
    $result = WasenderApi::leaveGroup('abc');
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Left');
});

test('acceptGroupInvite returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups/invite/accept' => Http::response(['success' => true, 'message' => 'Accepted'], 200),
    ]);
    $result = WasenderApi::acceptGroupInvite('CODE123');
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Accepted');
});

test('getGroupInviteLink returns link', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups/abc/invite-link' => Http::response(['success' => true, 'link' => 'https://chat.whatsapp.com/xyz'], 200),
    ]);
    $result = WasenderApi::getGroupInviteLink('abc');
    expect($result['success'])->toBeTrue();
    expect($result['link'])->toBe('https://chat.whatsapp.com/xyz');
});

test('getGroupProfilePicture returns url', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/groups/abc/picture' => Http::response(['success' => true, 'url' => 'https://img'], 200),
    ]);
    $result = WasenderApi::getGroupProfilePicture('abc');
    expect($result['success'])->toBeTrue();
    expect($result['url'])->toBe('https://img');
});

test('uploadMediaFile from base64 returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/upload' => Http::response(['success' => true, 'id' => 'media123'], 200),
    ]);
    $result = WasenderApi::uploadMediaFile('data:image/png;base64,AAA', 'image/png');
    expect($result['success'])->toBeTrue();
    expect($result['id'])->toBe('media123');
});

test('decryptMediaFile returns decrypted payload', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/decrypt-media' => Http::response(['success' => true, 'data' => ['foo' => 'bar']], 200),
    ]);
    $result = WasenderApi::decryptMediaFile(['message' => ['imageMessage' => []]]);
    expect($result['success'])->toBeTrue();
    expect($result['data'])->toBe(['foo' => 'bar']);
});

test('sendPresenceUpdate returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/send-presence-update' => Http::response(['success' => true, 'message' => 'Presence sent'], 200),
    ]);
    $result = WasenderApi::sendPresenceUpdate('123@s.whatsapp.net', 'composing', 1000);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Presence sent');
});

test('sendPresenceUpdate throws on invalid type', function () {
    WasenderApi::sendPresenceUpdate('123@s.whatsapp.net', 'invalid');
})->throws(InvalidArgumentException::class);

test('editMessage returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/messages/123' => Http::response(['success' => true, 'message' => 'Edited'], 200),
    ]);
    $result = WasenderApi::editMessage(123, 'Updated text');
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Edited');
});

test('editMessage throws on empty text', function () {
    WasenderApi::editMessage(123, '');
})->throws(InvalidArgumentException::class);

test('deleteMessage returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/messages/123' => Http::response(['success' => true, 'message' => 'Deleted'], 200),
    ]);
    $result = WasenderApi::deleteMessage(123);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Deleted');
});

test('deleteMessage throws on invalid id', function () {
    WasenderApi::deleteMessage(0);
})->throws(InvalidArgumentException::class);

test('getMessageInfo returns info', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/messages/123/info' => Http::response(['success' => true, 'info' => []], 200),
    ]);
    $result = WasenderApi::getMessageInfo(123);
    expect($result['success'])->toBeTrue();
    expect($result['info'])->toBeArray();
});

test('getMessageInfo throws on invalid id', function () {
    WasenderApi::getMessageInfo(0);
})->throws(InvalidArgumentException::class);

test('regenerateApiKey returns success', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/whatsapp-sessions/1/regenerate-api-key' => Http::response(['success' => true, 'message' => 'Regenerated'], 200),
    ]);
    $result = WasenderApi::regenerateApiKey(1);
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Regenerated');
});

test('getSessionStatus returns status', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/sessions/1/status' => Http::response(['success' => true, 'status' => 'CONNECTED'], 200),
    ]);
    $result = WasenderApi::getSessionStatus('1');
    expect($result['success'])->toBeTrue();
    expect($result['status'])->toBe('CONNECTED');
});

test('getSessionUserInfo returns info', function () {
    Http::fake([
        'https://www.wasenderapi.com/api/user' => Http::response(['success' => true, 'user' => ['id' => 1]], 200),
    ]);
    $result = WasenderApi::getSessionUserInfo();
    expect($result['success'])->toBeTrue();
    expect($result['user'])->toBe(['id' => 1]);
});

test('webhook dispatches event', function () {
    Event::fake();
    $payload = [
        'event' => 'messages.upsert',
        'data' => ['foo' => 'bar'],
    ];
    $secret = 'testsecret';
    Config::set('wasenderapi.webhook_secret', $secret);
    $response = $this->postJson('/wasender/webhook', $payload, [
        'x-webhook-signature' => $secret,
    ]);
    $response->assertOk();
    Event::assertDispatched(MessagesUpserted::class, function ($event) use ($payload) {
        return $event->payload == $payload;
    });
}); 

test('webhook dispatches call received event', function () {
    Event::fake();
    $payload = [
        'event' => 'call.received',
        'data' => ['foo' => 'bar'],
    ];
    $secret = 'testsecret';
    Config::set('wasenderapi.webhook_secret', $secret);
    $response = $this->postJson('/wasender/webhook', $payload, [
        'x-webhook-signature' => $secret,
    ]);
    $response->assertOk();
    Event::assertDispatched(CallReceived::class, function ($event) use ($payload) {
        return $event->payload == $payload;
    });
});

test('webhook dispatches generic event when unmapped', function () {
    Event::fake();
    $payload = [
        'event' => 'unknown.event',
        'data' => ['foo' => 'bar'],
    ];
    $secret = 'testsecret';
    Config::set('wasenderapi.webhook_secret', $secret);
    $response = $this->postJson('/wasender/webhook', $payload, [
        'x-webhook-signature' => $secret,
    ]);
    $response->assertOk();
    Event::assertDispatched(WasenderWebhookEvent::class, function ($event) use ($payload) {
        return $event->event === 'unknown.event' && $event->payload == $payload;
    });
});