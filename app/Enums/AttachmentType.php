<?php

namespace App\Enums;

use App\Concerns\EnumHelper;

enum AttachmentType: string
{
    use EnumHelper;

    case ISSUE = 'issue';
    case COMPLETION = 'completion';
}
