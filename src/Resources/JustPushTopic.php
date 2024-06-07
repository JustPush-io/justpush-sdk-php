<?php

declare(strict_types=1);

namespace JustPush\Resources;

use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use JustPush\Base\JustPushBase;
use JustPush\Exceptions\JustPushValidationException;
use RuntimeException;

class JustPushTopic extends JustPushBase
{
    public const ENDPOINT = '/topics';

    /**
     * @var array|null
     */
    private ?array $topicParams = null;
    private ?string $topicUuid  = null;

    /**
     * @param $token
     */
    public function __construct($token)
    {
        $this->setToken($token);

        return $this;
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
     * @param string|null $title
     *
     * @return $this
     */
    public function title(?string $title = null): static
    {
        $this->topicParams['title'] = $title ?? 'Default';

        return $this;
    }

    /**
     * @param string|null $topicUuid
     *
     * @return $this
     */
    public function topic(?string $topicUuid = null): static
    {
        $this->topicUuid = $topicUuid;

        return $this;
    }

    /**
     * @param string|null $url
     * @param string|null $body
     *
     * @throws JustPushValidationException
     *
     * @return $this
     */
    public function avatar(?string $url = null, ?string $body = null): static
    {
        if (null !== $url && null !== $body) {
            throw new JustPushValidationException();
        }

        if (null !== $url) {
            $this->topicParams['avatar']['external_url'] = $url;
        }

        if (null !== $body) {
            $this->topicParams['avatar']['body'] = $body;
        }

        return $this;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function create(): array
    {
        try {
            $response = $this->client()->request('POST', self::ENDPOINT, [
                'headers' => $this->baseHeaders(),
                'json'    => $this->topicParams,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            // Handle specific Guzzle exceptions and rethrow or log as necessary
            throw new RuntimeException('Failed to create topic: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return array
     */
    public function get(): array
    {
        if (empty($this->topicUuid)) {
            throw new InvalidArgumentException('Topic token must be set before updating');
        }

        try {
            $response = $this->client()->request('GET', self::ENDPOINT . '/' . $this->topicUuid, [
                'headers' => $this->baseHeaders(),
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            // Handle specific Guzzle exceptions and rethrow or log as necessary
            throw new RuntimeException('Failed to get message: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return array
     */
    public function update(): array
    {
        if (empty($this->topicUuid)) {
            throw new InvalidArgumentException('Topic token must be set before updating');
        }

        var_dump('TopicParams: ', $this->topicParams);

        try {
            $response = $this->client()->request('PUT', self::ENDPOINT . '/' . $this->topicUuid, [
                'headers' => $this->baseHeaders(),
                'json'    => $this->topicParams,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            // Handle specific Guzzle exceptions and rethrow or log as necessary
            throw new RuntimeException('Failed to get message: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return array
     */
    public function getTopicParams(): array
    {
        return $this->topicParams;
    }
}
