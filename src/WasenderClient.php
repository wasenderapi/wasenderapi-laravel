<?php

namespace WasenderApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;
use WasenderApi\Exceptions\WasenderApiException;
use WasenderApi\Data\SendTextMessageData;
use WasenderApi\Data\RetryConfig;

class WasenderClient
{
    protected string $apiKey;
    protected string $baseUrl;
    protected ?string $personalAccessToken;

    public function __construct()
    {
        $this->apiKey = Config::get('wasenderapi.api_key', env('WASENDERAPI_API_KEY'));
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
        if ($to instanceof \WasenderApi\Data\SendImageMessageData) {
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
        if ($to instanceof \WasenderApi\Data\SendVideoMessageData) {
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
     * @param array $options
     * @param RetryConfig|null $retryConfig
     * @return array
     * @throws WasenderApiException
     */
    public function sendDocument($to, $url = null, ?string $caption = null, array $options = [], ?RetryConfig $retryConfig = null): array
    {
        if ($to instanceof \WasenderApi\Data\SendDocumentMessageData) {
            $payload = [
                'to' => $to->to,
                'documentUrl' => $to->documentUrl,
            ];
            if ($to->text) $payload['text'] = $to->text;
        } else {
            $payload = [
                'to' => $to,
                'documentUrl' => $url,
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
        if ($to instanceof \WasenderApi\Data\SendAudioMessageData) {
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
        if ($to instanceof \WasenderApi\Data\SendStickerMessageData) {
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
        if ($to instanceof \WasenderApi\Data\SendContactMessageData) {
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
        if ($to instanceof \WasenderApi\Data\SendLocationMessageData) {
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

    // Groups
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

    // Sessions
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
        return $this->get("/sessions/{$sessionId}/status", true);
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