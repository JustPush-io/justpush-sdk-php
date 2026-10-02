<?php

declare(strict_types=1);

namespace JustPush\Base;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JustPush\Exceptions\JustPushApiException;
use JustPush\Exceptions\JustPushConnectionException;
use Psr\Http\Message\ResponseInterface;

class JustPushBase
{
    /**
     * The API url.
     */
    public const JUSTPUSH_API_URL = 'https://api.justpush.io';
    public const CLIENT_VERSION   = '1.1.0';

    /**
     * Response headers that describe the plan's monthly message allowance.
     */
    public const RATE_LIMIT_HEADERS = ['X-Limit-App-Limit', 'X-Limit-App-Remaining', 'X-Limit-App-Reset'];

    public ?array $headers         = [];
    public ?array $responseHeaders = null;
    public ?array $result          = null;

    protected ?Client $httpClient = null;

    public function client(): Client
    {
        return $this->httpClient ??= new Client([
            'base_uri' => self::JUSTPUSH_API_URL,
            'timeout'  => 10,
        ]);
    }

    /**
     * Use your own Guzzle client, e.g. with another base_uri for a local API, or a mock handler in tests.
     */
    public function withClient(Client $client): static
    {
        $this->httpClient = $client;

        return $this;
    }

    public function setToken(string $token): static
    {
        $this->headers['Authorization'] = 'Bearer ' . $token;

        return $this;
    }

    public function baseHeaders(): array
    {
        $this->headers['Accept']     = 'application/json';
        $this->headers['User-Agent'] = 'JustPushAPIClient ' . self::CLIENT_VERSION;

        return $this->headers;
    }

    public function result(): ?array
    {
        return $this->result;
    }

    /**
     * The X-Limit-App-* headers of the last response, e.g. ['X-Limit-App-Remaining' => ['9895']].
     */
    public function responseHeaders(): ?array
    {
        if (null === $this->responseHeaders) {
            return null;
        }

        $filtered = [];

        foreach (self::RATE_LIMIT_HEADERS as $name) {
            foreach ($this->responseHeaders as $key => $value) {
                if (0 === strcasecmp((string) $key, $name)) {
                    $filtered[$name] = $value;
                }
            }
        }

        return $filtered;
    }

    /**
     * Send a request and store the decoded result and headers.
     *
     * @throws JustPushApiException        when the API answers with an error status
     * @throws JustPushConnectionException when the API can't be reached
     */
    protected function send(string $method, string $uri, ?array $json, string $action): static
    {
        $options = [
            'headers'     => $this->baseHeaders(),
            'http_errors' => false,
        ];

        if (null !== $json) {
            $options['json'] = $json;
        }

        try {
            $response = $this->client()->request($method, $uri, $options);
        } catch (GuzzleException $e) {
            throw new JustPushConnectionException('Failed to ' . $action . ': ' . $e->getMessage(), (int) $e->getCode(), $e);
        }

        $this->responseHeaders = $response->getHeaders();
        $this->result          = $this->decode($response);

        if ($response->getStatusCode() >= 400) {
            throw JustPushApiException::fromResponse($action, $response->getStatusCode(), $this->result);
        }

        return $this;
    }

    private function decode(ResponseInterface $response): ?array
    {
        $body = (string) $response->getBody();

        if ('' === $body) {
            return null;
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : ['raw' => $body];
    }
}
