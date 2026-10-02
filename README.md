<p align="center"><img src="https://cdn.justpush.io/core/app%20icon_nobackground.svg" width="150" height="auto"></p>

## JustPush - PHP SDK

Welcome to the official PHP SDK for JustPush! This SDK allows you to easily integrate with our powerful messaging platform, providing functionalities to create messages, retrieve messages, create topics, and update topics.

## Features

- **Create Messages**: Send messages effortlessly using our streamlined API.
- **Retrieve Messages**: Fetch messages with ease for seamless integration and processing.
- **Create Topics**: Organize your messages by creating specific topics.
- **Update Topics**: Modify existing topics to keep your message structure flexible and up-to-date.

## Download the App in the App Stores

## Installation

Install the SDK via Composer:

```bash
composer require justpush/justpush-php-sdk

```
## Basic Push Message
This is a basic example of sending a notification. 
````php
$response = JustPushMessage::token('REPLACE_WITH_USER_TOKEN')
    ->message('Here is a sample Message')
    ->title('Test Title')
    ->create();

echo json_encode($response->result(), JSON_PRETTY_PRINT); //Result
echo json_encode($response->responseHeaders(), JSON_PRETTY_PRINT); //Response Headers
````

# JustPush Message

| Function Name           | Available Attributes                                                                                                                                                                                              | Description                                                              |
|-------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------|
| `token`                 | `string $token`                                                                                                                                                                                                   | Set the user token / API token                                           |
| `message`               | `string $message`                                                                                                                                                                                                 | The textual body of the message                                          |
| `title`                 | `string $title`                                                                                                                                                                                                   | The title of the message                                                 |
| `topic`                 | `string $topic`                                                                                                                                                                                                   | The name of the topic to send the message to (created if it doesn't exist) |
| `topicToken`            | `string $topicToken`                                                                                                                                                                                              | Target a topic by its API token (`api_token` from `JustPushTopic`) instead of its name |
| `image`                 | `string $url`, `?string $caption`                                                                                                                                                                                 | Adds an image the to the message                                         |
| `images`                | `array $images`                                                                                                                                                                                                   | Adds multiple images, each `['url' => …]` or `['body' => base64]`, with an optional `caption` |
| `imageData`             | `string $contents`, `?string $caption`                                                                                                                                                                            | Attaches an image from its contents (e.g. a camera snapshot)             |
| `imageFile`             | `string $path`, `?string $caption`                                                                                                                                                                                | Attaches an image file from disk                                         |
| `button`                | `string $cta`, `string $url`, `bool $actionRequired`                                                                                                                                                              | Adds a button to the message                                             |
| `buttons`               | `array $buttons`                                                                                                                                                                                                  | Adds multiple buttons to the message                                     | 
| `sound`                 | `string $sound`                                                                                                                                                                                                   | The sound name in any case, see `JustPushMessage::SOUNDS`; `none` is silent |
| `buttonGroup`           | `string $name`, `string $cta`, `array $buttons`, `bool $actionRequired`                                                                                                                                           | A button that opens a list of up to 10 buttons (at most 4 groups)        |
| `priority`              | `int\|string $priority`                                                                                                                                                                                           | `2`, `1`, `0`, `-1`, `-2`, or `highest`, `high`, `normal`, `low`, `lowest` |
| `highestPriority`       |                                                                                                                                                                                                                   | Set the message priority on `2`                                          |                                         
| `highPriority`          |                                                                                                                                                                                                                   | Set the message priority on `1`                                          | 
| `normalPriority`        |                                                                                                                                                                                                                   | Set the message priority on `0`                                          |
| `lowPriority`           |                                                                                                                                                                                                                   | Set the message priority on `-1`                                         |
| `lowestPriority`        |                                                                                                                                                                                                                   | Set the message priority on `-2`                                         |
| `expiry`                | `int $expiry`                                                                                                                                                                                                     | Set the expiry in Seconds | 
| `acknowledge`           | `bool $requiresAcknowledgement`, `bool $requiresRetry = false`, `int $retryInterval = 0`, `int $maxRetries = 0`, `bool $callbackRequired = false`, `?string $callbackUrl = null`, `?array $callbackParams = null` | Adds an acknowledgement to the messages |

### Defining the topic
- **`topic('Name')`**: the topic with that name is used. If you don't have one yet, it's created. If several topics share the name, the message goes to your default topic.
- **`topicToken('…')`**: the topic with that API token (`api_token` in the `JustPushTopic` result).
- Neither: the message goes to your default topic.

### Limits
Up to 10 buttons, 4 button groups (10 buttons each) and 10 images. Button labels are cut at 25 characters and titles at 255.
With `acknowledge()` retries, the interval is 10–65535 seconds (default 60) and max retries 0–255 (default 10).
The SDK throws a `JustPushValidationException` before sending when a message breaks these rules.

### Sending multiple images
When a message contains multiple images, the first image will be used for the push message banner. 

### Setting an Expiry
When an expiry is set, the message will have an TTL in seconds. After the expiry, in seconds, has expired, the message will automatically be hidden.

# JustPush Topics

| Function Name      | Available Attributes                        |
|--------------------|---------------------------------------------|
| `__construct`      | `$token`                                    |
| `token`            | `string $token`                             |
| `title`            | `?string $title`                            |
| `topic`            | `?string $topicUuid`                        |
| `avatar`           | `?string $url`, `?string $body`             |

## POST / Create A Topic
This is a basic example of creating a topic
````php
$response = JustPushTopic::token('REPLACE_WITH_USER_TOKEN')
    ->title('New Topic')
    ->create();
    
echo json_encode($response->result(), JSON_PRETTY_PRINT); //Result
echo json_encode($response->responseHeaders(), JSON_PRETTY_PRINT); //Response Headers

````

## PUT / Update A Topic
This is a basic example of updating a topic
````php
$response = JustPushTopic::token('REPLACE_WITH_USER_TOKEN')
    ->topic('REPLACE_WITH_TOPIC_UUID')
    ->title('New Topic Title')
    ->update();
    
echo json_encode($response->result(), JSON_PRETTY_PRINT); //Result
echo json_encode($response->responseHeaders(), JSON_PRETTY_PRINT); //Response Headers

````

## GET / Get a topic
This is a basic example of creating a topic
````php
$response = JustPushTopic::token('REPLACE_WITH_USER_TOKEN')
    ->topic('REPLACE_WITH_TOPIC_UUID')
    ->get();

echo json_encode($response->result(), JSON_PRETTY_PRINT); //Result
echo json_encode($response->responseHeaders(), JSON_PRETTY_PRINT); //Response Headers
````

### Response Headers
| Key                         | Value            | Description                                                                      |
|-----------------------------|------------------|----------------------------------------------------------------------------------|
| ```X-Limit-App-Limit```     | ```["10000"]```  | The amount of messages that you can send based on your active subscription       |
| ```X-Limit-App-Remaining``` | ```["9895"]```   | The amount of messages you have left for the current period in your subscription |
| ```X-Limit-App-Reset```     | ```["234512"]``` | The seconds till the monthly reset will be done.                                 |

## Errors
| Exception | When |
|-----------|------|
| `JustPush\Exceptions\JustPushValidationException` | The message or topic is invalid; nothing was sent. Extends `InvalidArgumentException`. |
| `JustPush\Exceptions\JustPushApiException` | The API returned an error. `getStatus()`, `getErrors()` (per-field, for a 422), `getBody()`, plus `isUnauthorized()`, `isValidationError()` and `isRateLimited()`. Extends `RuntimeException`. |
| `JustPush\Exceptions\JustPushConnectionException` | The API couldn't be reached or timed out (10 seconds). Extends `RuntimeException`. |

Use `withClient(new \GuzzleHttp\Client([...]))` to change the timeout, point at another API URL or mock requests in tests.


## OpenApi Spec
The package comes with an OpenAPI spec. Which can be found in the `docs` folder. [Click Here](https://github.com/JustPush-io/justpush-sdk-php/tree/docs)

## Changelog
- 1.1.0
  - Fixed: button `action_required`, button groups and the acknowledgement retry interval were sent with field names the API ignores. They now work.
  - Fixed: `acknowledge()` with retries sent an interval of 0, which the API rejects. It now defaults to 60 seconds and 10 retries.
  - Fixed: `priority('HIGH')` and other names didn't work.
  - Fixed: `JustPushTopic::update()` printed debug output.
  - Fixed: `JustPushValidationException` couldn't be autoloaded.
  - Fixed: `responseHeaders()` failed before a request was made.
  - Added: `topicToken()`, `imageData()`, `imageFile()`, `withClient()`, input validation, and typed exceptions that carry the API's error message and status.
  - Requires PHP 8.1 or newer.
- 1.0.17 - Added Button Groups
- 1.0.15 - Added retry mechanism for `acknowledgements` 