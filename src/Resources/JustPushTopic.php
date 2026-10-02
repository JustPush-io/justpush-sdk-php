<?php

declare(strict_types=1);

namespace JustPush\Resources;

use InvalidArgumentException;
use JustPush\Base\JustPushBase;
use JustPush\Exceptions\JustPushApiException;
use JustPush\Exceptions\JustPushConnectionException;
use JustPush\Exceptions\JustPushValidationException;

class JustPushTopic extends JustPushBase
{
    public const ENDPOINT = '/topics';

    private ?array $topicParams = null;
    private ?string $topicUuid  = null;

    public function __construct($token)
    {
        $this->setToken($token);
    }

    public static function token(string $token = ''): static
    {
        return new static($token);
    }

    /**
     * The topic's title, at most 100 characters.
     */
    public function title(?string $title = null): static
    {
        $title ??= 'Default';

        if (mb_strlen($title) > 100) {
            throw new JustPushValidationException('A topic title can be at most 100 characters.');
        }

        $this->topicParams['title'] = $title;

        return $this;
    }

    /**
     * The topic's UUID, for get() and update().
     */
    public function topic(?string $topicUuid = null): static
    {
        $this->topicUuid = $topicUuid;

        return $this;
    }

    /**
     * Set the avatar from a public URL, or from base64-encoded image data in $body.
     *
     * @throws JustPushValidationException when both or neither are given
     */
    public function avatar(?string $url = null, ?string $body = null): static
    {
        if ((null === $url) === (null === $body)) {
            throw new JustPushValidationException('Pass either an avatar url or a body, not both.');
        }

        $this->topicParams['avatar'] = null !== $url ? ['external_url' => $url] : ['body' => $body];

        return $this;
    }

    /**
     * Create the topic. result() then holds its uuid, title, slug, avatar and api_token.
     *
     * @throws JustPushApiException        when the API rejects the topic
     * @throws JustPushConnectionException when the API can't be reached
     */
    public function create(): static
    {
        if (empty($this->topicParams['title'])) {
            throw new JustPushValidationException('A topic needs a title.');
        }

        return $this->send('POST', self::ENDPOINT, $this->topicParams, 'create topic');
    }

    /**
     * Fetch one of your topics. Set topic() first.
     *
     * @throws JustPushApiException        e.g. 404 when there's no topic with that UUID
     * @throws JustPushConnectionException when the API can't be reached
     */
    public function get(): static
    {
        return $this->send('GET', $this->topicPath('getting'), null, 'get topic');
    }

    /**
     * Rename the topic or change its avatar. Set topic() first. The default topic can't be renamed.
     *
     * @throws JustPushApiException        e.g. 403 when you don't own the topic
     * @throws JustPushConnectionException when the API can't be reached
     */
    public function update(): static
    {
        return $this->send('PUT', $this->topicPath('updating'), $this->topicParams ?? [], 'update topic');
    }

    public function getTopicParams(): array
    {
        return $this->topicParams ?? [];
    }

    private function topicPath(string $action): string
    {
        if (empty($this->topicUuid)) {
            throw new InvalidArgumentException('Topic UUID must be set before ' . $action);
        }

        return self::ENDPOINT . '/' . rawurlencode($this->topicUuid);
    }
}
