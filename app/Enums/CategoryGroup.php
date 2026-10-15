<?php

namespace App\Enums;

enum CategoryGroup: string
{
    case Grooming = 'grooming';
    case Health = 'health';
    case Education = 'education';
    case Care = 'care';
    case Adoption = 'adoption';
    case Shop = 'shop';
    case Places = 'places';
}
