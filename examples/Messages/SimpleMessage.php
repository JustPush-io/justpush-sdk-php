<?php

declare(strict_types=1);

use JustPush\Resources\JustPushMessage;

require '../../vendor/autoload.php';

$response = JustPushMessage::token('REPLACE_WITH_API_TOKEN')
    ->message('Here is a sample Message')
    ->title('Test Title')
    ->create();

echo json_encode($response->result(), JSON_PRETTY_PRINT);
echo json_encode($response->responseHeaders(), JSON_PRETTY_PRINT);
