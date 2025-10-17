<?php

namespace WasenderApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;
use InvalidArgumentException;
use WasenderApi\Exceptions\WasenderApiException;
use WasenderApi\Data\SendTextMessageData;
use WasenderApi\Data\RetryConfig;
use WasenderApi\Data\SendImageMessageData;
use WasenderApi\Data\SendVideoMessageData;
use WasenderApi\Data\SendDocumentMessageData;
use WasenderApi\Data\SendAudioMessageData;
use WasenderApi\Data\SendStickerMessageData;
use WasenderApi\Data\SendContactMessageData;
use WasenderApi\Data\SendLocationMessageData;

class WasenderClient
{
    protected string $apiKey;
    protected string $baseUrl;
    protected ?string $personalAccessToken;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? Config::get('wasenderapi.api_key', env('WASENDERAPI_API_KEY'));
        $this->baseUrl = rtrim(Config::get('wasenderapi.base_url', env('WASENDERAPI_BASE_URL', 'https://www.wasenderapi.com/api')), '/');
        $this->personalAccessToken = Config::get('wasenderapi.personal_access_token', env('WASENDERAPI_PERSONAL_ACCESS_TOKEN'));
    }

    /**
     * Send a text message.
     *
     * @param string|SendTextMessageData $to
     * @param string|null $text
     * @param array $options
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     *
     * Usage (DI):
     *   $client->sendText('123', 'hi');
     *   $client->sendText(new SendTextMessageData('123', 'hi'));
     * Usage (Facade):
     *   WasenderApi::sendText('123', 'hi');
     */
    public function sendText($to, $text = null, array $options = [], ?RetryConfig $retryConfig = null): array
    {
        if ($to instanceof SendTextMessageData) {
            $payload = [
                'to' => $to->to,
                'text' => $to->text,
            ];
        } else {
            $payload = [
                'to' => $to,
                'text' => $text,
            ];
        }
        $payload = array_merge($options, $payload);
        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    /**
     * Send a text message with user mentions to a group.
     *
     * @param string $to
     * @param string $text
     * @param array $mentions
     * @param array $options
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     */
    public function sendMessageWithMentions(string $to, string $text, array $mentions, array $options = [], ?RetryConfig $retryConfig = null): array
    {
        $payload = array_merge($options, [
            'to' => $to,
            'text' => $text,
            'mentions' => $mentions,
        ]);

        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    /**
     * Send a quoted message replying to an existing message.
     *
     * @param string $to
     * @param int $replyTo
     * @param string|null $text
     * @param array $options
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     */
    public function sendQuotedMessage(string $to, int $replyTo, ?string $text = null, array $options = [], ?RetryConfig $retryConfig = null): array
    {
        if ($replyTo <= 0) {
            throw new InvalidArgumentException('replyTo must be a positive message identifier.');
        }

        $payload = array_merge($options, [
            'to' => $to,
            'replyTo' => $replyTo,
        ]);

        if ($text !== null) {
            $payload['text'] = $text;
        }

        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    /**
     * Send an image message.
     *
     * @param string|SendImageMessageData $to
     * @param string|null $url
     * @param string|null $caption
     * @param array $options
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     */
    public function sendImage($to, $url = null, ?string $caption = null, array $options = [], ?RetryConfig $retryConfig = null): array
    {
        if ($to instanceof SendImageMessageData) {
            $payload = [
                'to' => $to->to,
                'imageUrl' => $to->imageUrl,
            ];
            if ($to->text) $payload['text'] = $to->text;
        } else {
            $payload = [
                'to' => $to,
                'imageUrl' => $url,
            ];
            if ($caption) $payload['text'] = $caption;
        }
        $payload = array_merge($options, $payload);
        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    /**
     * Send a video message.
     *
     * @param string|SendVideoMessageData $to
     * @param string|null $url
     * @param string|null $caption
     * @param array $options
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     */
    public function sendVideo($to, $url = null, ?string $caption = null, array $options = [], ?RetryConfig $retryConfig = null): array
    {
        if ($to instanceof SendVideoMessageData) {
            $payload = [
                'to' => $to->to,
                'videoUrl' => $to->videoUrl,
            ];
            if ($to->text) $payload['text'] = $to->text;
        } else {
            $payload = [
                'to' => $to,
                'videoUrl' => $url,
            ];
            if ($caption) $payload['text'] = $caption;
        }
        $payload = array_merge($options, $payload);
        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    /**
     * Send a document message.
     *
     * @param string|SendDocumentMessageData $to
     * @param string|null $url
     * @param string|null $caption
     * @param string|null $fileName
     * @param array $options
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     */
    public function sendDocument($to, $url = null, ?string $caption = null, ?string $fileName = null, array $options = [], ?RetryConfig $retryConfig = null): array
    {
        if ($to instanceof SendDocumentMessageData) {
            $payload = [
                'to' => $to->to,
                'documentUrl' => $to->documentUrl,
                'fileName' => $to->fileName,
            ];
            if ($to->text) $payload['text'] = $to->text;
        } else {
            $payload = [
                'to' => $to,
                'documentUrl' => $url,
                'fileName' => $fileName,
            ];
            if ($caption) $payload['text'] = $caption;
        }
        $payload = array_merge($options, $payload);
        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    /**
     * Send an audio message.
     *
     * @param string|SendAudioMessageData $to
     * @param string|null $url
     * @param array $options
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     */
    public function sendAudio($to, $url = null, array $options = [], ?RetryConfig $retryConfig = null): array
    {
        if ($to instanceof SendAudioMessageData) {
            $payload = [
                'to' => $to->to,
                'audioUrl' => $to->audioUrl,
            ];
        } else {
            $payload = [
                'to' => $to,
                'audioUrl' => $url,
            ];
        }
        $payload = array_merge($options, $payload);
        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    /**
     * Send a sticker message.
     *
     * @param string|SendStickerMessageData $to
     * @param string|null $url
     * @param array $options
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     */
    public function sendSticker($to, $url = null, array $options = [], ?RetryConfig $retryConfig = null): array
    {
        if ($to instanceof SendStickerMessageData) {
            $payload = [
                'to' => $to->to,
                'stickerUrl' => $to->stickerUrl,
            ];
        } else {
            $payload = [
                'to' => $to,
                'stickerUrl' => $url,
            ];
        }
        $payload = array_merge($options, $payload);
        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    /**
     * Send a contact message.
     *
     * @param string|SendContactMessageData $to
     * @param string|null $contactName
     * @param string|null $contactPhone
     * @param array $options
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     */
    public function sendContact($to, $contactName = null, $contactPhone = null, array $options = [], ?RetryConfig $retryConfig = null): array
    {
        if ($to instanceof SendContactMessageData) {
            $payload = [
                'to' => $to->to,
                'contact' => [
                    'name' => $to->contactName,
                    'phone' => $to->contactPhone,
                ],
            ];
        } else {
            $payload = [
                'to' => $to,
                'contact' => [
                    'name' => $contactName,
                    'phone' => $contactPhone,
                ],
            ];
        }
        $payload = array_merge($options, $payload);
        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    /**
     * Send a location message.
     *
     * @param string|SendLocationMessageData $to
     * @param float|null $latitude
     * @param float|null $longitude
     * @param string|null $name
     * @param string|null $address
     * @param array $options
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     */
    public function sendLocation($to, $latitude = null, $longitude = null, ?string $name = null, ?string $address = null, array $options = [], ?RetryConfig $retryConfig = null): array
    {
        if ($to instanceof SendLocationMessageData) {
            $location = [
                'latitude' => $to->latitude,
                'longitude' => $to->longitude,
            ];
            if ($to->name) $location['name'] = $to->name;
            if ($to->address) $location['address'] = $to->address;
            $payload = [
                'to' => $to->to,
                'messageType' => 'location',
                'location' => $location,
            ];
        } else {
            $location = [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
            if ($name) $location['name'] = $name;
            if ($address) $location['address'] = $address;
            $payload = [
                'to' => $to,
                'messageType' => 'location',
                'location' => $location,
            ];
        }
        $payload = array_merge($options, $payload);
        return $this->postWithRetry('/send-message', $payload, false, $retryConfig);
    }

    // Contacts
    /**
     * Get all contacts.
     *
     * @return array
     * @throws WasenderApiException
     */
    public function getContacts(): array
    {
        return $this->get('/contacts');
    }
    /**
     * Get contact info.
     *
     * @param string $phone
     * @return array
     * @throws WasenderApiException
     */
    public function getContactInfo(string $phone): array
    {
        return $this->get("/contacts/{$phone}");
    }
    /**
     * Get contact profile picture.
     *
     * @param string $phone
     * @return array
     * @throws WasenderApiException
     */
    public function getContactProfilePicture(string $phone): array
    {
        return $this->get("/contacts/{$phone}/profile-picture");
    }
    /**
     * Block a contact.
     *
     * @param string $phone
     * @return array
     * @throws WasenderApiException
     */
    public function blockContact(string $phone): array
    {
        return $this->post("/contacts/{$phone}/block");
    }
    /**
     * Unblock a contact.
     *
     * @param string $phone
     * @return array
     * @throws WasenderApiException
     */
    public function unblockContact(string $phone): array
    {
        return $this->post("/contacts/{$phone}/unblock");
    }

    /**
     * Check if a phone number is registered on WhatsApp.
     *
     * @param string $phoneNumber
     * @return array
     * @throws WasenderApiException
     */
    public function checkIfOnWhatsapp(string $phoneNumber): array
    {
        $encoded = rawurlencode($phoneNumber);
        return $this->get("/on-whatsapp/{$encoded}");
    }

    // Groups
    /**
     * Create a new group.
     *
     * @param string $name
     * @param array $participants
     * @return array
     * @throws WasenderApiException
     */
    public function createGroup(string $name, array $participants = []): array
    {
        if ($name === '') {
            throw new InvalidArgumentException('Group name cannot be empty.');
        }

        $payload = ['name' => $name];
        if (!empty($participants)) {
            $payload['participants'] = array_values($participants);
        }

        return $this->post('/groups', $payload);
    }

    /**
     * Get all groups.
     *
     * @return array
     * @throws WasenderApiException
     */
    public function getGroups(): array
    {
        return $this->get('/groups');
    }
    /**
     * Get group metadata.
     *
     * @param string $jid
     * @return array
     * @throws WasenderApiException
     */
    public function getGroupMetadata(string $jid): array
    {
        return $this->get("/groups/{$jid}/metadata");
    }
    /**
     * Get group participants.
     *
     * @param string $jid
     * @return array
     * @throws WasenderApiException
     */
    public function getGroupParticipants(string $jid): array
    {
        return $this->get("/groups/{$jid}/participants");
    }
    /**
     * Add group participants.
     *
     * @param string $jid
     * @param array $participants
     * @return array
     * @throws WasenderApiException
     */
    public function addGroupParticipants(string $jid, array $participants): array
    {
        $payload = ['participants' => $participants];
        return $this->post("/groups/{$jid}/participants/add", $payload);
    }
    /**
     * Remove group participants.
     *
     * @param string $jid
     * @param array $participants
     * @return array
     * @throws WasenderApiException
     */
    public function removeGroupParticipants(string $jid, array $participants): array
    {
        $payload = ['participants' => $participants];
        return $this->post("/groups/{$jid}/participants/remove", $payload);
    }
    /**
     * Update (promote/demote) group participants.
     *
     * @param string $jid
     * @param string $action
     * @param array $participants
     * @return array
     * @throws WasenderApiException
     */
    public function updateGroupParticipants(string $jid, string $action, array $participants): array
    {
        $action = strtolower($action);
        if (!in_array($action, ['promote', 'demote'], true)) {
            throw new InvalidArgumentException('Action must be either promote or demote.');
        }

        $payload = [
            'action' => $action,
            'participants' => $participants,
        ];

        return $this->put("/groups/{$jid}/participants/update", $payload);
    }
    /**
     * Update group settings.
     *
     * @param string $jid
     * @param array $settings
     * @return array
     * @throws WasenderApiException
     */
    public function updateGroupSettings(string $jid, array $settings): array
    {
        return $this->put("/groups/{$jid}/settings", $settings);
    }

    /**
     * Retrieve information about a group invite using its code.
     *
     * @param string $inviteCode
     * @return array
     * @throws WasenderApiException
     */
    public function getGroupInviteInfo(string $inviteCode): array
    {
        return $this->get("/groups/invite/{$inviteCode}");
    }

    /**
     * Leave a group.
     *
     * @param string $jid
     * @return array
     * @throws WasenderApiException
     */
    public function leaveGroup(string $jid): array
    {
        return $this->post("/groups/{$jid}/leave");
    }

    /**
     * Accept a group invite code.
     *
     * @param string $code
     * @return array
     * @throws WasenderApiException
     */
    public function acceptGroupInvite(string $code): array
    {
        return $this->post('/groups/invite/accept', ['code' => $code]);
    }

    /**
     * Retrieve a group's invite link.
     *
     * @param string $jid
     * @return array
     * @throws WasenderApiException
     */
    public function getGroupInviteLink(string $jid): array
    {
        return $this->get("/groups/{$jid}/invite-link");
    }

    /**
     * Retrieve a group's profile picture.
     *
     * @param string $jid
     * @return array
     * @throws WasenderApiException
     */
    public function getGroupProfilePicture(string $jid): array
    {
        return $this->get("/groups/{$jid}/picture");
    }

    /**
     * Upload a media file either from a local path or base64 string.
     *
     * When providing a local file path, you may pass additional multipart fields via $options
     * (e.g. ['mimetype' => 'image/png']). Set the 'filename' key to override the uploaded filename.
     *
     * @param string $fileOrBase64
     * @param string|null $mimeType
     * @param array $options
     * @return array
     * @throws WasenderApiException
     */
    public function uploadMediaFile(string $fileOrBase64, ?string $mimeType = null, array $options = []): array
    {
        $url = $this->baseUrl . '/upload';
        $token = $this->apiKey;

        if (is_file($fileOrBase64)) {
            $filename = $options['filename'] ?? basename($fileOrBase64);
            $stream = fopen($fileOrBase64, 'rb');
            if ($stream === false) {
                throw new InvalidArgumentException('Unable to open file for upload.');
            }

            $request = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'User-Agent' => 'wasenderapi-laravel-sdk',
            ]);

            foreach ($options as $key => $value) {
                if ($key === 'filename') {
                    continue;
                }
                $request = $request->attach($key, (string)$value);
            }

            $response = $request->attach('file', $stream, $filename)->post($url);

            if (is_resource($stream)) {
                fclose($stream);
            }
        } else {
            $payload = array_merge($options, ['base64' => $fileOrBase64]);
            if ($mimeType) {
                $payload['mimetype'] = $mimeType;
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'User-Agent' => 'wasenderapi-laravel-sdk',
            ])->post($url, $payload);
        }

        if (!$response->successful()) {
            throw new WasenderApiException('Wasender API error: ' . $response->body(), $response->status(), $response->json());
        }

        return $response->json();
    }

    /**
     * Decrypt a media file payload returned by Wasender.
     *
     * @param array $data
     * @return array
     * @throws WasenderApiException
     */
    public function decryptMediaFile(array $data): array
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Decrypt media payload cannot be empty.');
        }

        return $this->post('/decrypt-media', ['data' => $data]);
    }

    /**
     * Send a presence update for a given JID.
     *
     * @param string $jid
     * @param string $type
     * @param int|null $delayMs
     * @param array $options
     * @return array
     * @throws WasenderApiException
     */
    public function sendPresenceUpdate(string $jid, string $type, ?int $delayMs = null, array $options = []): array
    {
        $allowed = ['composing', 'recording', 'available', 'unavailable'];
        $type = strtolower($type);

        if (!in_array($type, $allowed, true)) {
            throw new InvalidArgumentException('Presence type must be one of: ' . implode(', ', $allowed));
        }

        $payload = array_merge($options, [
            'jid' => $jid,
            'type' => $type,
        ]);

        if ($delayMs !== null) {
            $payload['delayMs'] = $delayMs;
        }

        return $this->post('/send-presence-update', $payload);
    }

    // Messages
    /**
     * Edit an existing message's text content.
     *
     * @param int $messageId
     * @param string $text
     * @return array
     * @throws WasenderApiException
     */
    public function editMessage(int $messageId, string $text): array
    {
        if ($messageId <= 0) {
            throw new InvalidArgumentException('Message id must be positive.');
        }
        if ($text === '') {
            throw new InvalidArgumentException('Message text cannot be empty.');
        }

        return $this->put("/messages/{$messageId}", ['text' => $text]);
    }

    /**
     * Delete a previously sent message.
     *
     * @param int $messageId
     * @return array
     * @throws WasenderApiException
     */
    public function deleteMessage(int $messageId): array
    {
        if ($messageId <= 0) {
            throw new InvalidArgumentException('Message id must be positive.');
        }

        return $this->delete("/messages/{$messageId}");
    }

    /**
     * Retrieve detailed information for a message.
     *
     * @param int $messageId
     * @return array
     * @throws WasenderApiException
     */
    public function getMessageInfo(int $messageId): array
    {
        if ($messageId <= 0) {
            throw new InvalidArgumentException('Message id must be positive.');
        }

        return $this->get("/messages/{$messageId}/info");
    }

    // Sessions
    /**
     * Retrieve information about the authenticated session user.
     *
     * @return array
     * @throws WasenderApiException
     */
    public function getSessionUserInfo(): array
    {
        return $this->get('/user', false);
    }

    /**
     * Get all WhatsApp sessions.
     *
     * @return array
     * @throws WasenderApiException
     */
    public function getAllWhatsAppSessions(): array
    {
        return $this->get('/whatsapp-sessions', true);
    }
    /**
     * Create a WhatsApp session.
     *
     * @param array $payload
     * @return array
     * @throws WasenderApiException
     */
    public function createWhatsAppSession(array $payload): array
    {
        return $this->post('/whatsapp-sessions', $payload, true);
    }
    /**
     * Get WhatsApp session details.
     *
     * @param int $sessionId
     * @return array
     * @throws WasenderApiException
     */
    public function getWhatsAppSessionDetails(int $sessionId): array
    {
        return $this->get("/whatsapp-sessions/{$sessionId}", true);
    }
    /**
     * Update WhatsApp session.
     *
     * @param int $sessionId
     * @param array $payload
     * @return array
     * @throws WasenderApiException
     */
    public function updateWhatsAppSession(int $sessionId, array $payload): array
    {
        return $this->put("/whatsapp-sessions/{$sessionId}", $payload, true);
    }
    /**
     * Delete WhatsApp session.
     *
     * @param int $sessionId
     * @return array
     * @throws WasenderApiException
     */
    public function deleteWhatsAppSession(int $sessionId): array
    {
        return $this->delete("/whatsapp-sessions/{$sessionId}", true);
    }
    /**
     * Connect WhatsApp session.
     *
     * @param int $sessionId
     * @param bool $qrAsImage
     * @return array
     * @throws WasenderApiException
     */
    public function connectWhatsAppSession(int $sessionId, bool $qrAsImage = false): array
    {
        $query = $qrAsImage ? '?qrAsImage=true' : '';
        return $this->post("/whatsapp-sessions/{$sessionId}/connect{$query}", null, true);
    }
    /**
     * Get WhatsApp session QR code.
     *
     * @param int $sessionId
     * @return array
     * @throws WasenderApiException
     */
    public function getWhatsAppSessionQrCode(int $sessionId): array
    {
        return $this->get("/whatsapp-sessions/{$sessionId}/qr-code", true);
    }
    /**
     * Disconnect WhatsApp session.
     *
     * @param int $sessionId
     * @return array
     * @throws WasenderApiException
     */
    public function disconnectWhatsAppSession(int $sessionId): array
    {
        return $this->post("/whatsapp-sessions/{$sessionId}/disconnect", null, true);
    }
    /**
     * Regenerate API key for session.
     *
     * @param int $sessionId
     * @return array
     * @throws WasenderApiException
     */
    public function regenerateApiKey(int $sessionId): array
    {
        return $this->post("/whatsapp-sessions/{$sessionId}/regenerate-api-key", null, true);
    }
    /**
     * Get session status.
     *
     * @param string $sessionId
     * @return array
     * @throws WasenderApiException
     */
    public function getSessionStatus(string $sessionId): array
    {
        return $this->get("/sessions/{$sessionId}/status", false);
    }

    // --- HTTP helpers ---
    protected function get(string $path, bool $usePersonalToken = false): array
    {
        return $this->request('GET', $path, null, $usePersonalToken);
    }
    protected function post(string $path, ?array $payload = null, bool $usePersonalToken = false): array
    {
        return $this->request('POST', $path, $payload, $usePersonalToken);
    }
    protected function put(string $path, array $payload, bool $usePersonalToken = false): array
    {
        return $this->request('PUT', $path, $payload, $usePersonalToken);
    }
    protected function delete(string $path, bool $usePersonalToken = false): array
    {
        return $this->request('DELETE', $path, null, $usePersonalToken);
    }
    protected function request(string $method, string $path, ?array $payload = null, bool $usePersonalToken = false): array
    {
        if ($usePersonalToken && empty($this->personalAccessToken)) {
            throw new WasenderApiException('this endpoint requires a personal access token');
        }
        $url = $this->baseUrl . $path;
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . ($usePersonalToken && $this->personalAccessToken ? $this->personalAccessToken : $this->apiKey),
            'User-Agent' => 'wasenderapi-laravel-sdk',
        ];
        $response = Http::withHeaders($headers);
        if ($payload !== null) {
            $response = $response->withBody(json_encode($payload), 'application/json');
        }
        $resp = $response->send($method, $url, $payload ? ['json' => $payload] : []);
        if (!$resp->successful()) {
            throw new WasenderApiException('Wasender API error: ' . $resp->body(), $resp->status(), $resp->json());
        }
        return $resp->json();
    }

    /**
     * Helper for retrying send-message endpoints on 429.
     *
     * @param string $path
     * @param array $payload
     * @param bool $usePersonalToken
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     */
    protected function postWithRetry(string $path, array $payload, bool $usePersonalToken = false, ?RetryConfig $retryConfig = null): array
    {
        $attempts = 0;
        $maxRetries = $retryConfig?->maxRetries ?? 0;
        $enabled = $retryConfig?->enabled ?? false;
        do {
            $attempts++;
            try {
                return $this->post($path, $payload, $usePersonalToken);
            } catch (WasenderApiException $e) {
                if ($enabled && $e->getCode() === 429 && $attempts <= $maxRetries) {
                    $retryAfter = $e->getResponse()['retry_after'] ?? 1;
                    sleep((int)$retryAfter);
                    continue;
                }
                throw $e;
            }
        } while ($enabled && $attempts <= $maxRetries);
        throw new WasenderApiException('Max retries exceeded', 429);
    }
} 