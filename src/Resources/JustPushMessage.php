<?php

declare(strict_types=1);

namespace JustPush\Resources;

use InvalidArgumentException;
use JustPush\Base\JustPushBase;
use JustPush\Exceptions\JustPushApiException;
use JustPush\Exceptions\JustPushConnectionException;
use JustPush\Exceptions\JustPushValidationException;

class JustPushMessage extends JustPushBase
{
    public const ENDPOINT = '/messages';

    public const PRIORITIES = [
        'HIGHEST' => 2,
        'HIGH'    => 1,
        'NORMAL'  => 0,
        'LOW'     => -1,
        'LOWEST'  => -2,
    ];

    public const SOUNDS = [
        'none', 'default', 'bike', 'bugle', 'cashregister', 'classical', 'cosmic', 'falling', 'gamelan',
        'incoming', 'intermission', 'magic', 'mechanical', 'pianobar', 'siren', 'spacealarm', 'tugboat',
        'alien', 'climb', 'persistent', 'echo', 'updown', 'vibrate',
    ];

    /**
     * The messageParams.
     */
    private ?array $messageParams = null;

    public function __construct($token)
    {
        $this->setToken($token);
    }

    public static function token(string $token = ''): static
    {
        return new static($token);
    }

    /**
     * The key of a message you sent, for get().
     */
    public function key(string $messageKey = ''): static
    {
        $this->messageParams['key'] = $messageKey;

        return $this;
    }

    public function message(string $message = ''): static
    {
        $this->messageParams['message'] = $message;

        return $this;
    }

    public function title(string $title): static
    {
        $this->messageParams['title'] = $title;

        return $this;
    }

    /**
     * The topic's name. An existing topic with that name is used, otherwise a new one is created.
     * To target a topic by its API token instead, use topicToken().
     */
    public function topic(string $topic): static
    {
        $this->messageParams['topic'] = $topic;
        unset($this->messageParams['topic_token']);

        return $this;
    }

    /**
     * Target a topic by its API token (the "api_token" JustPushTopic returns).
     */
    public function topicToken(string $topicToken): static
    {
        $this->messageParams['topic_token'] = $topicToken;
        unset($this->messageParams['topic']);

        return $this;
    }

    /**
     * @deprecated Pass the token to token() instead. Kept for backwards compatibility.
     */
    public function user(string $user): static
    {
        $this->messageParams['user'] = $user;

        return $this;
    }

    /**
     * Attach an image by its public URL. The first image becomes the notification banner.
     */
    public function image(string $url, ?string $caption = null): static
    {
        return $this->addImage(['url' => $url], $caption);
    }

    /**
     * Attach an image from its contents (JPEG, PNG, …), e.g. a camera snapshot.
     */
    public function imageData(string $contents, ?string $caption = null): static
    {
        return $this->addImage(['body' => base64_encode($contents)], $caption);
    }

    /**
     * Attach an image file from disk.
     */
    public function imageFile(string $path, ?string $caption = null): static
    {
        $contents = @file_get_contents($path);

        if (false === $contents) {
            throw new JustPushValidationException('Could not read image file: ' . $path);
        }

        return $this->imageData($contents, $caption);
    }

    /**
     * @param array<array{url?: string, body?: string, caption?: string|null}> $images
     */
    public function images(array $images): static
    {
        foreach ($images as $image) {
            if (!empty($image['url'])) {
                $this->image($image['url'], $image['caption'] ?? null);
            } elseif (!empty($image['body'])) {
                $this->addImage(['body' => $image['body']], $image['caption'] ?? null);
            } else {
                throw new JustPushValidationException('Every image needs a url or a body.');
            }
        }

        return $this;
    }

    /**
     * Add a button (at most 10). With $actionRequired the message stays pending until it's tapped.
     */
    public function button(string $cta, string $url, bool $actionRequired = false): static
    {
        if (count($this->messageParams['buttons'] ?? []) >= 10) {
            throw new JustPushValidationException('A message can have at most 10 buttons.');
        }

        $this->messageParams['buttons'][] = [
            'cta'             => $cta,
            'url'             => $url,
            'action_required' => $actionRequired,
        ];

        return $this;
    }

    /**
     * @param array<array{cta: string, url: string, action_required?: bool, actionRequired?: bool}> $buttons
     */
    public function buttons(array $buttons): static
    {
        foreach ($buttons as $button) {
            $this->button(
                cta: $button['cta'],
                url: $button['url'],
                actionRequired: (bool) ($button['action_required'] ?? $button['actionRequired'] ?? false)
            );
        }

        return $this;
    }

    /**
     * Add a button (at most 4 groups) that opens a named list of buttons (at most 10 each).
     *
     * @param array<array{cta: string, url: string}> $buttons
     */
    public function buttonGroup(string $name, string $cta, array $buttons, bool $actionRequired = false): static
    {
        if (count($this->messageParams['button_groups'] ?? []) >= 4) {
            throw new JustPushValidationException('A message can have at most 4 button groups.');
        }

        if (count($buttons) > 10) {
            throw new JustPushValidationException('A button group can hold at most 10 buttons.');
        }

        $this->messageParams['button_groups'][] = [
            'name'            => $name,
            'cta'             => $cta,
            'action_required' => $actionRequired,
            'buttons'         => array_map(
                static fn (array $button): array => ['cta' => $button['cta'], 'url' => $button['url']],
                array_values($buttons)
            ),
        ];

        return $this;
    }

    /**
     * A sound name in any case, e.g. "cashregister" or "COSMIC". "none" is silent.
     */
    public function sound(string $sound): static
    {
        $sound = strtolower(trim($sound));

        if (!in_array($sound, self::SOUNDS, true)) {
            throw new JustPushValidationException('Unknown sound "' . $sound . '". Use one of: ' . implode(', ', self::SOUNDS));
        }

        $this->messageParams['sound'] = $sound;

        return $this;
    }

    /**
     * -2 to 2, or "lowest", "low", "normal", "high", "highest".
     */
    public function priority(int|string $priority): static
    {
        if (is_string($priority)) {
            $name = strtoupper(trim($priority));

            if (preg_match('/^-?\d+$/', $name)) {
                $priority = (int) $name;
            } elseif (array_key_exists($name, self::PRIORITIES)) {
                $priority = self::PRIORITIES[$name];
            } else {
                throw new JustPushValidationException('Unknown priority "' . $priority . '".');
            }
        }

        if ($priority < -2 || $priority > 2) {
            throw new JustPushValidationException('Priority must be between -2 and 2.');
        }

        $this->messageParams['priority'] = $priority;

        return $this;
    }

    public function highestPriority(): static
    {
        return $this->priority(2);
    }

    public function highPriority(): static
    {
        return $this->priority(1);
    }

    public function normalPriority(): static
    {
        return $this->priority(0);
    }

    public function lowPriority(): static
    {
        return $this->priority(-1);
    }

    public function lowestPriority(): static
    {
        return $this->priority(-2);
    }

    /**
     * Hide the message after this many seconds.
     */
    public function expiry(int $expiry): static
    {
        if ($expiry < 0) {
            throw new JustPushValidationException('The expiry must be 0 seconds or more.');
        }

        $this->messageParams['expiry_ttl'] = $expiry;

        return $this;
    }

    /**
     * Ask for the message to be acknowledged on a device.
     *
     * With $requiresRetry the message is re-sent every $retryInterval seconds (10–65535, default 60),
     * at most $maxRetries times (0–255, default 10). With $callbackRequired, JustPush calls
     * $callbackUrl with $callbackParams once the message is acknowledged.
     */
    public function acknowledge(
        bool $requiresAcknowledgement,
        bool $requiresRetry = false,
        int $retryInterval = 0,
        int $maxRetries = 0,
        bool $callbackRequired = false,
        ?string $callbackUrl = null,
        ?array $callbackParams = null
    ): static {
        $this->messageParams['requires_acknowledgement'] = $requiresAcknowledgement;
        unset($this->messageParams['acknowledgement']);

        if (!$requiresAcknowledgement) {
            return $this;
        }

        if ($requiresRetry) {
            // 0 used to be sent as-is, which the API rejects (the minimum interval is 10 seconds).
            $retryInterval = $retryInterval > 0 ? $retryInterval : 60;
            $maxRetries    = $maxRetries > 0 ? $maxRetries : 10;

            if ($retryInterval < 10 || $retryInterval > 65535) {
                throw new JustPushValidationException('The retry interval must be 10–65535 seconds.');
            }

            if ($maxRetries > 255) {
                throw new JustPushValidationException('Max retries must be 255 or less.');
            }

            $this->messageParams['acknowledgement']['requires_retry'] = true;
            $this->messageParams['acknowledgement']['interval']       = $retryInterval;
            $this->messageParams['acknowledgement']['max_retries']    = $maxRetries;
        }

        if ($callbackRequired) {
            if (empty($callbackUrl)) {
                throw new JustPushValidationException('A callback needs a callback URL.');
            }

            $this->messageParams['acknowledgement']['callback']['required'] = true;
            $this->messageParams['acknowledgement']['callback']['url']      = $callbackUrl;

            if (null !== $callbackParams) {
                $this->messageParams['acknowledgement']['callback']['params'] = json_encode($callbackParams, JSON_THROW_ON_ERROR);
            }
        }

        return $this;
    }

    /**
     * Send the message. result() then holds ['status' => 1, 'key' => '…'].
     *
     * @throws JustPushApiException        when the API rejects the message
     * @throws JustPushConnectionException when the API can't be reached
     */
    public function create(): static
    {
        $params = $this->messageParams ?? [];
        unset($params['key']);

        if (empty($params['message']) && empty($params['title'])) {
            throw new JustPushValidationException('A message needs a message or a title.');
        }

        return $this->send('POST', self::ENDPOINT, $params, 'create message');
    }

    /**
     * Fetch a message you sent, e.g. to check whether it was acknowledged. Set key() first.
     *
     * @throws JustPushApiException        e.g. 404 when there's no message with that key
     * @throws JustPushConnectionException when the API can't be reached
     */
    public function get(): static
    {
        if (empty($this->messageParams['key'])) {
            throw new InvalidArgumentException('Message key must be set before calling get.');
        }

        return $this->send('GET', self::ENDPOINT . '/' . rawurlencode($this->messageParams['key']), null, 'get message');
    }

    public function getMessageParams(): array
    {
        return $this->messageParams ?? [];
    }

    private function addImage(array $image, ?string $caption): static
    {
        if (count($this->messageParams['images'] ?? []) >= 10) {
            throw new JustPushValidationException('A message can have at most 10 images.');
        }

        if (null !== $caption) {
            $image['caption'] = $caption;
        }

        $this->messageParams['images'][] = $image;

        return $this;
    }
}
