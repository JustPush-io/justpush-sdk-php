<?php

declare(strict_types=1);

namespace JustPush\Exceptions;

use InvalidArgumentException;

/**
 * The message or topic is invalid, caught before anything is sent.
 */
class JustPushValidationException extends InvalidArgumentException {}
