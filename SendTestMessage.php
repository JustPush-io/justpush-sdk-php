<?php

declare(strict_types=1);

use JustPush\Resources\JustPushMessage;

require 'vendor/autoload.php';

$response = JustPushMessage::token('h9dpYu2xmLORpiHIGsB1z7AGwSaRSBvi')
    ->message('Here is a sample Message')
    ->title('Test Title')
    ->lowPriority()
    ->button(
        cta: 'Button 1',
        url: 'https://google.com',
        actionRequired: true
    )
    ->create();

echo json_encode($response, JSON_PRETTY_PRINT);

sleep(5);

$message = JustPushMessage::token('h9dpYu2xmLORpiHIGsB1z7AGwSaRSBvi')
    ->key(messageKey: $response['key'])
    ->get();

echo json_encode($message, JSON_PRETTY_PRINT);
