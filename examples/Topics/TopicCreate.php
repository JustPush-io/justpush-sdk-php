<?php

declare(strict_types=1);

use JustPush\Resources\JustPushTopic;

require '../../vendor/autoload.php';

$response = JustPushTopic::token('IggUNJlmz3ULZvH0ur2UF2yR2UAws8jU')
    ->title('New Topic')
    ->create();

echo json_encode($response, JSON_PRETTY_PRINT);
