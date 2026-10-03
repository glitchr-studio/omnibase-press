<?php

namespace Base\Press\Enum;

/**
 * The three biographies a presenter asks for: the one of a season brochure
 * (about 100 words), the one of a programme note (about 250), the full one.
 */
enum BioLength: string
{
    case SHORT = 'short';
    case MEDIUM = 'medium';
    case LONG = 'long';

    /** The words it is expected to hold; null: as long as it needs. */
    public function words(): ?int
    {
        return match ($this) {
            self::SHORT => 100,
            self::MEDIUM => 250,
            self::LONG => null,
        };
    }
}
