<?php

namespace App\Enums;

enum ProviderSource: string
{
    case Osm = 'osm';
    case Bce = 'bce';
    case Registry = 'registry';
    case Manual = 'manual';
}
