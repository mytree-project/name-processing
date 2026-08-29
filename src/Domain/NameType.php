<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Domain;

enum NameType: string
{
    case GivenName = 'given_name';
    case Surname = 'surname';
    case PlaceName = 'place_name';
    case Other = 'other';
}
