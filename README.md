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
ccomposer require justpush/justpush-php-sdk

```
## Basic Push Message
This is a basic example of sending a notification. 
````php
$response = JustPushMessage::token('REPLACE_WITH_USER_TOKEN')
    ->message('Here is a sample Message')
    ->title('Test Title')
    ->create();
````

# JustPush Message

| Function Name           | Available Attributes                                                                 | Description                                                              |
|-------------------------|--------------------------------------------------------------------------------------|--------------------------------------------------------------------------|
| `token`                 | `string $token`                                                                      | Set the user token / API token                                           |
| `message`               | `string $message`                                                                    | The textual body of the message                                          |
| `title`                 | `string $title`                                                                      | The title of the message                                                 |
| `topic`                 | `string $topic`                                                                      | Either the UUID or the name of the topic you want to send the message to |
| `image`                 | `string $url`, `?string $caption`                                                    | Adds an image the to the message                                         |
| `images`                | `array $images`                                                                      | Adds multiple images                                                     
| `button`                | `string $cta`, `string $url`, `bool $actionRequired`                                 | Adds a button to the message                                             |
| `buttons`               | `array $buttons`                                                                     | Adds multiple buttons to the message                                     | 
| `sound`                 | `string $sound`                                                                      | Define the sound of the message                                          |
| `priority`              | `int $priority`                                                               | Manually set the priority, `2`, `1`, `0`, `-1`, `-2`                     |
| `highestPriority`       |                                                                                      | Set the message priority on `2`                                          |                                         
| `highPriority`          |                                                                                      | Set the message priority on `1`                                          | 
| `normalPriority`        |                                                                                      | Set the message priority on `0`                                          |
| `lowPriority`           |                                                                                      | Set the message priority on `-1`                                         |
| `lowestPriority`        |                                                                                      | Set the message priority on `-2`                                         |
| `expiry`                | `int $expiry`                                                                        | Set the expiry in Seconds | 
| `acknowledge`           | `bool $requiresAcknowledgement`, `bool $callbackRequired`, `?string $callbackUrl`, `?array $callbackParams` | Adds an acknowledgement to the messages |

### Defining the topic
Our goals it to keep the API as simple as possible. Therefore, you can send either:
- **Topic Title** - When the title exists more than once, the oldest topic will be used. If the name is not in your topic list, a new topic will be created. 
- **Topic UUID** - Uses the exact match of the topic

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
````

## PUT / Update A Topic
This is a basic example of updating a topic
````php
$response = JustPushTopic::token('REPLACE_WITH_USER_TOKEN')
    ->topic('REPLACE_WITH_TOPIC_UUID')
    ->title('New Topic Title')
    ->update();
````

## GET / Get a topic
This is a basic example of creating a topic
````php
$response = JustPushTopic::token('REPLACE_WITH_USER_TOKEN')
    ->topic('REPLACE_WITH_TOPIC_UUID')
    ->get();
````

## OpenApi Spec
The package comes with an OpenAPI spec. Which can be found in the `docs` folder. [Click Here](https://github.com/JustPush-io/justpush-sdk-php/tree/docs)

## Changelog
- `n/a` - No changes yet. 