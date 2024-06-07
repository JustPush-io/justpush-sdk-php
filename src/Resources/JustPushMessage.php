<?php

declare(strict_types=1);

namespace JustPush\Resources;

use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use JustPush\Base\JustPushBase;
use RuntimeException;
use Stringable;

class JustPushMessage extends JustPushBase
{
    public const ENDPOINT = '/message';

    /**
     * The messageParams.
     */
    private ?array $messageParams = null;

    public function __construct($token)
    {
        $this->setToken($token);
    }

    /**
     * @param string $token
     *
     * @return static
     */
    public static function token(string $token = ''): static
    {
        return new static($token);
    }

    /**
     * @param string $messageKey
     *
     * @return $this
     */
    public function key(string $messageKey = ''): static
    {
        $this->messageParams['key'] = $messageKey;

        return $this;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function message(string $message = ''): static
    {
        $this->messageParams['message'] = $message;

        return $this;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function title(string $title): static
    {
        $this->messageParams['title'] = $title;

        return $this;
    }

    /**
     * @param string $topic
     *
     * @return $this
     */
    public function topic(string $topic): static
    {
        $this->messageParams['topic'] = $topic;

        return $this;
    }

    /**
     * @param string $user
     *
     * @return $this
     */
    public function user(string $user): static
    {
        $this->messageParams['user'] = $user;

        return $this;
    }

    /**
     * @param string $url
     * @param string|null $caption
     *
     * @return $this
     */
    public function image(string $url, ?string $caption = null): static
    {
        $this->messageParams['images'][] = [
            'url'     => $url,
            'caption' => $caption,
        ];

        return $this;
    }

    /**
     * @param array $images
     *
     * @return $this
     */
    public function images(array $images): static
    {
        foreach ($images as $image) {
            $this->image(
                url: $image['url'],
                caption: $image['caption']
            );
        }

        return $this;
    }

    /**
     * @param string $cta
     * @param string $url
     * @param bool $actionRequired
     *
     * @return $this
     */
    public function button(string $cta, string $url, bool $actionRequired = false): static
    {
        $this->messageParams['buttons'][] = [
            'url'            => $url,
            'actionRequired' => $actionRequired,
            'cta'            => $cta,
        ];

        return $this;
    }

    /**
     * @param array $buttons
     *
     * @return $this
     */
    public function buttons(array $buttons): static
    {
        foreach ($buttons as $button) {
            $this->button(
                cta: $button['cta'],
                url: $button['url']
            );
        }

        return $this;
    }

    /**
     * @param string $sound
     *
     * @return $this
     */
    public function sound(string $sound): static
    {
        $this->messageParams['sounds'] = $sound;

        return $this;
    }

    /**
     * @param string $priority
     *
     * @return $this
     */
    public function priority(int|string $priority): static
    {
        if ($priority instanceof Stringable) {
            $priority = match ($priority) {
                'HIGHEST' => 2,
                'HIGH'    => 1,
                'LOW'     => -1,
                'LOWEST'  => -2,
                default   => 0,
            };
        }

        $this->messageParams['priority'] = $priority;

        return $this;
    }

    /**
     * @return $this
     */
    public function highestPriority(): static
    {
        $this->priority(2);

        return $this;
    }

    /**
     * @return $this
     */
    public function highPriority(): static
    {
        $this->priority(1);

        return $this;
    }

    /**
     * @return $this
     */
    public function normalPriority(): static
    {
        $this->priority(0);

        return $this;
    }

    /**
     * @return $this
     */
    public function lowPriority(): static
    {
        $this->priority(-1);

        return $this;
    }

    /**
     * @return $this
     */
    public function lowestPriority(): static
    {
        $this->priority(-2);

        return $this;
    }

    /**
     * @param int $expiry
     *
     * @return $this
     */
    public function expiry(int $expiry): static
    {
        $this->messageParams['expiry_ttl'] = $expiry;

        return $this;
    }

    /**
     * @param bool $requiresAcknowledgement
     * @param bool $callbackRequired
     * @param string|null $callbackUrl
     * @param array|null $callbackParams
     *
     * @return $this
     */
    public function acknowledge(bool $requiresAcknowledgement, bool $callbackRequired = false, ?string $callbackUrl = null, ?array $callbackParams = null): static
    {
        $this->messageParams['acknowledgement']['requires_acknowledgement'] = $requiresAcknowledgement;

        if ($callbackRequired) {
            $this->messageParams['acknowledgement']['callback_required'] = $callbackRequired;
            $this->messageParams['acknowledgement']['callback_url']      = $callbackUrl;
            $this->messageParams['acknowledgement']['callback_params']   = $callbackParams;
        }

        return $this;
    }

    /**
     * @throws RuntimeException
     *
     * @return array
     */
    public function create(): array
    {
        try {
            $response = $this->client()->request('POST', self::ENDPOINT, [
                'headers' => $this->baseHeaders(),
                'json'    => $this->messageParams,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            // Handle specific Guzzle exceptions and rethrow or log as necessary
            throw new RuntimeException('Failed to create message: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws RuntimeException
     *
     * @return array
     */
    public function get(): array
    {
        if (empty($this->messageParams['key'])) {
            throw new InvalidArgumentException('Message key must be set before calling get.');
        }

        try {
            $response = $this->client()->request('GET', self::ENDPOINT . '/' . $this->messageParams['key'], [
                'headers' => $this->baseHeaders(),
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            // Handle specific Guzzle exceptions and rethrow or log as necessary
            throw new RuntimeException('Failed to get message: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getMessageParams(): array
    {
        return $this->messageParams;
    }
}
