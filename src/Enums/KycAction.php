<?php

namespace Fintech\Ekyc\Enums;

use Fintech\Core\Traits\EnumHasSerialization;

enum KycAction: string
{
    use EnumHasSerialization;

    case Verification = 'verify';
    case StatusCheck = 'status';
    case Cancellation = 'delete';
}
