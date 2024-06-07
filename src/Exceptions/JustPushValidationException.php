<?php

declare(strict_types=1);

namespace JustPush\Exceptions;

use Exception;

class JustPushValidationException extends Exception
{
    public function render(): void {}
}
