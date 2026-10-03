<?php

namespace Base\Press\Enum;

/**
 * Who speaks: a paper or a magazine, a colleague (a conductor, a fellow
 * soloist), someone in the hall. A string enum (not omnibase's EnumType) so
 * the column reads plainly in the database and in the admin's filters.
 */
enum QuoteKind: string
{
    case PRESS = 'press';
    case COLLEAGUE = 'colleague';
    case AUDIENCE = 'audience';
}
