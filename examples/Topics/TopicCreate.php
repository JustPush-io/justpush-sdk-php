<?php

declare(strict_types=1);

use JustPush\Resources\JustPushTopic;

require '../../vendor/autoload.php';

$response = JustPushTopic::token('REPLACE_WITH_API_TOKEN')
    ->title('New Topic')
    ->create();

echo json_encode($response->result(), JSON_PRETTY_PRINT);
echo json_encode($response->responseHeaders(), JSON_PRETTY_PRINT);
