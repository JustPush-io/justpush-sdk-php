<?php

declare(strict_types=1);

namespace JustPush\Exceptions;

use RuntimeException;

/**
 * The API couldn't be reached, or it didn't answer in time.
 */
class JustPushConnectionException extends RuntimeException {}
