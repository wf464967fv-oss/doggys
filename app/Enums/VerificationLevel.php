<?php

namespace App\Enums;

enum VerificationLevel: string
{
    case None = 'none';
    case Verified = 'verified';
    case Regulated = 'regulated';
}
