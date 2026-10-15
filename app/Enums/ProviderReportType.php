<?php

namespace App\Enums;

enum ProviderReportType: string
{
    case Error = 'error';
    case RemovalRequest = 'removal_request';
}
