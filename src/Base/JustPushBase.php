<?php

declare(strict_types=1);

namespace JustPush\Base;

use GuzzleHttp\Client;

class JustPushBase
{
    /**
     * The API url.
     */
    public const JUSTPUSH_API_URL = 'https://api.justpush.test';
    public const CLIENT_VERSION   = '1.0.6';

    public ?array $headers = [];
    public ?array $responseHeaders = null;
    public ?array $result = null;

    public function client(): Client
    {
        return new Client([
            'base_uri' => self::JUSTPUSH_API_URL,
        ]);
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

    public function result() : ?array {
        return $this->result;
    }

    public function responseHeaders() : ?array {

        $requiredHeaders = ['X-Limit-App-Limit', 'X-Limit-App-Remaining', 'X-Limit-App-Reset'];

        foreach($this->responseHeaders as $key => $value) {
            if(!in_array($key, $requiredHeaders)) {
                unset($this->responseHeaders[$key]);
            }
        }
        return $this->responseHeaders;
    }
}
