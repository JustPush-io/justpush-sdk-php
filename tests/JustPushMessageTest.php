<?php

declare(strict_types=1);

namespace JustPush\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JustPush\Exceptions\JustPushApiException;
use JustPush\Exceptions\JustPushConnectionException;
use JustPush\Exceptions\JustPushValidationException;
use JustPush\Resources\JustPushMessage;
use JustPush\Resources\JustPushTopic;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
#[CoversNothing]
final class JustPushMessageTest extends TestCase
{
    private array $history = [];

    public function testSendsTheFieldNamesTheApiAccepts(): void
    {
        $message = JustPushMessage::token('tok')
            ->withClient($this->client(new Response(201, ['X-Limit-App-Remaining' => '9895'], '{"status":1,"key":"abc"}')))
            ->title('Leak')
            ->message('Water detected')
            ->topic('Home')
            ->priority('HIGH')
            ->sound('SIREN')
            ->button('Open', 'https://example.com', true)
            ->buttons([['cta' => 'Two', 'url' => 'https://two.example', 'actionRequired' => true]])
            ->buttonGroup('Links', 'More', [['cta' => 'A', 'url' => 'https://a.example']])
            ->image('https://example.com/a.jpg')
            ->images([['url' => 'https://example.com/b.jpg']])
            ->imageData("\x89PNG", 'Cam')
            ->expiry(60)
            ->acknowledge(true, true, 0, 0, true, 'https://example.com/cb', ['a' => 1])
            ->create();

        self::assertSame(['status' => 1, 'key' => 'abc'], $message->result());
        self::assertSame(['X-Limit-App-Remaining' => ['9895']], $message->responseHeaders());

        $request = $this->history[0]['request'];
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/messages', $request->getUri()->getPath());
        self::assertSame('Bearer tok', $request->getHeaderLine('Authorization'));
        self::assertSame([
            'title'    => 'Leak',
            'message'  => 'Water detected',
            'topic'    => 'Home',
            'priority' => 1,
            'sound'    => 'siren',
            'buttons'  => [
                ['cta' => 'Open', 'url' => 'https://example.com', 'action_required' => true],
                ['cta' => 'Two', 'url' => 'https://two.example', 'action_required' => true],
            ],
            'button_groups' => [[
                'name'    => 'Links', 'cta' => 'More', 'action_required' => false,
                'buttons' => [['cta' => 'A', 'url' => 'https://a.example']],
            ]],
            'images' => [
                ['url' => 'https://example.com/a.jpg'],
                ['url'  => 'https://example.com/b.jpg'],
                ['body' => 'iVBORw==', 'caption' => 'Cam'],
            ],
            'expiry_ttl'               => 60,
            'requires_acknowledgement' => true,
            'acknowledgement'          => [
                'requires_retry' => true,
                'interval'       => 60,
                'max_retries'    => 10,
                'callback'       => ['required' => true, 'url' => 'https://example.com/cb', 'params' => '{"a":1}'],
            ],
        ], json_decode((string) $request->getBody(), true));
    }

    public function testTopicTokenReplacesTopic(): void
    {
        $params = JustPushMessage::token('tok')->message('x')->topic('Home')->topicToken('t0k')->getMessageParams();
        self::assertSame(['message' => 'x', 'topic_token' => 't0k'], $params);
    }

    #[DataProvider('priorities')]
    public function testPriorities(int|string $input, int $expected): void
    {
        self::assertSame($expected, JustPushMessage::token('t')->priority($input)->getMessageParams()['priority']);
    }

    public static function priorities(): array
    {
        return [[2, 2], ['highest', 2], ['LOW', -1], [' normal ', 0], ['-2', -2]];
    }

    #[DataProvider('invalid')]
    public function testValidation(callable $build): void
    {
        $this->expectException(JustPushValidationException::class);
        $build(JustPushMessage::token('t'));
    }

    public static function invalid(): array
    {
        return [
            'priority 3'      => [static fn ($m) => $m->priority(3)],
            'priority word'   => [static fn ($m) => $m->priority('loud')],
            'unknown sound'   => [static fn ($m) => $m->sound('kazoo')],
            'negative expiry' => [static fn ($m) => $m->expiry(-1)],
            '11 buttons'      => [static function ($m): void { for ($i = 0; $i < 11; $i++) { $m->button('b', 'https://x'); } }],
            '5 groups'        => [static function ($m): void {
                for ($i = 0; $i < 5; $i++) {
                    $m->buttonGroup('g', 'c', []);
                }
            }],
            '11 images' => [static function ($m): void {
                for ($i = 0; $i < 11; $i++) {
                    $m->image('https://x');
                }
            }],
            'image no source' => [static fn ($m) => $m->images([['caption' => 'x']])],
            'short interval'  => [static fn ($m) => $m->acknowledge(true, true, 5)],
            'callback no url' => [static fn ($m) => $m->acknowledge(true, false, 0, 0, true)],
            'empty message'   => [static fn ($m) => $m->create()],
        ];
    }

    public function testApiErrorsCarryStatusAndMessage(): void
    {
        $body = '{"message":"The sound is invalid.","errors":{"sound":["The sound is invalid."]}}';

        try {
            JustPushMessage::token('tok')->withClient($this->client(new Response(422, [], $body)))->message('x')->create();
            self::fail('No exception');
        } catch (JustPushApiException $e) {
            self::assertInstanceOf(RuntimeException::class, $e);
            self::assertTrue($e->isValidationError());
            self::assertSame('Failed to create message: The sound is invalid.', $e->getMessage());
            self::assertSame(['sound' => ['The sound is invalid.']], $e->getErrors());
        }

        try {
            JustPushMessage::token('bad')
                ->withClient($this->client(new Response(401, [], '{"error":{"type":"unauthorized","message":"Unauthenticated"}}')))
                ->key('abc')
                ->get();
            self::fail('No exception');
        } catch (JustPushApiException $e) {
            self::assertTrue($e->isUnauthorized());
            self::assertSame('Failed to get message: Unauthenticated', $e->getMessage());
        }
    }

    public function testConnectionErrors(): void
    {
        $this->expectException(JustPushConnectionException::class);
        JustPushMessage::token('tok')
            ->withClient($this->client(new ConnectException('down', new Request('POST', '/messages'))))
            ->message('x')
            ->create();
    }

    public function testGetMessageEncodesTheKey(): void
    {
        JustPushMessage::token('tok')->withClient($this->client(new Response(200, [], '{"key":"a b"}')))->key('a b')->get();
        self::assertSame('/messages/a%20b', $this->history[0]['request']->getUri()->getPath());
    }

    public function testTopics(): void
    {
        $topic = JustPushTopic::token('tok')
            ->withClient($this->client(new Response(201, [], '{"uuid":"t1","title":"Home"}'), new Response(200, [], '{"uuid":"t1"}')))
            ->title('Home')
            ->avatar(url: 'https://example.com/a.png')
            ->create();

        self::assertSame('t1', $topic->result()['uuid']);
        self::assertSame(['title' => 'Home', 'avatar' => ['external_url' => 'https://example.com/a.png']], json_decode((string) $this->history[0]['request']->getBody(), true));

        $topic->topic('t1')->update();
        self::assertSame('PUT', $this->history[1]['request']->getMethod());
        self::assertSame('/topics/t1', $this->history[1]['request']->getUri()->getPath());

        $this->expectException(JustPushValidationException::class);
        JustPushTopic::token('tok')->avatar('https://x', 'body');
    }

    public function testResponseHeadersBeforeARequest(): void
    {
        self::assertNull(JustPushMessage::token('t')->responseHeaders());
    }

    private function client(mixed ...$responses): Client
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new Client(['handler' => $stack, 'base_uri' => 'https://api.justpush.io']);
    }
}
