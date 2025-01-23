<?php

declare(strict_types=1);

namespace Bga\Games\Nertz\Models;

use Bga\Games\Nertz\Traits\EnumFromName;

enum Suit {
    case spade;
    case heart;
    case club;
    case diamond;

    use EnumFromName;
}
