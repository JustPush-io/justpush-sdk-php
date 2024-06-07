<?php

declare(strict_types=1);

use JustPush\Resources\JustPushMessage;

require 'vendor/autoload.php';

$response = JustPushMessage::token('IggUNJlmz3ULZvH0ur2UF2yR2UAws8jU')
    ->message('Here is a sample Message')
    ->title('Test Title')
    ->create();

echo json_encode($response, JSON_PRETTY_PRINT);
