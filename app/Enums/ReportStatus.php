<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Handled = 'handled';
    case Dismissed = 'dismissed';
}
