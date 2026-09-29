<?php

namespace App\Exceptions;

use RuntimeException;

class AccountPendingReactivationException extends RuntimeException
{
    public function __construct(
        public readonly string $phone,
        string $message = 'Your account is scheduled for deletion.'
    ) {
        parent::__construct($message, 409);
    }
}
