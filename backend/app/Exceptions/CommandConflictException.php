<?php

namespace App\Exceptions;

use RuntimeException;

class CommandConflictException extends RuntimeException
{
    public function __construct(public readonly int $latestRevision)
    {
        parent::__construct("월드 revision이 변경되었습니다. 최신 revision: {$latestRevision}");
    }
}
