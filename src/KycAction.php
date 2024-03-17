<?php

namespace Fintech\Ekyc\Enums;

use Fintech\Core\Traits\HasSerialization;

enum KycAction: string
{
    use HasSerialization;

    case Verification = 'verify';
    case StatusCheck = 'status';
    case Cancellation = 'delete';
}
