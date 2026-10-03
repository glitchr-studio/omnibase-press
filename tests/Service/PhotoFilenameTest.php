<?php

namespace Base\Press\Tests\Service;

use Base\Press\Service\PhotoFilename;
use PHPUnit\Framework\TestCase;

/** The name a downloaded picture is saved under: "<site>-<slug>.<ext>". */
final class PhotoFilenameTest extends TestCase
{
    public function testItIsTheSiteThenTheTitleThenTheExtension(): void
    {
        self::assertSame('clara-weiss-portrait-with-harp.jpg', PhotoFilename::of('Clara Weiss', 'Portrait with harp', '© Harald Hoffmann', 7, 'jpg'));
    }

    public function testAccentsAndSignsAreSpelledOut(): void
    {
        self::assertSame('helene-muller-a-l-opera.png', PhotoFilename::of('Hélène Müller', "À l'Opéra", null, 1, 'PNG'));
    }

    public function testWithNoTitleTheCreditNamesItWithoutItsSign(): void
    {
        self::assertSame('clara-weiss-harald-hoffmann.jpg', PhotoFilename::of('Clara Weiss', null, '© Harald Hoffmann', 7, 'jpeg'));
    }

    public function testWithNeitherItsNumberDoes(): void
    {
        self::assertSame('clara-weiss-photo-7.tiff', PhotoFilename::of('Clara Weiss', ' ', null, 7, 'tiff'));
        self::assertSame('photo.jpg', PhotoFilename::of(null, null, null, null, 'jpg'));
    }

    public function testAnExtensionCannotCarryAPath(): void
    {
        self::assertSame('site-title.jpg', PhotoFilename::of('Site', 'Title', null, 1, '../j/p.g'));
        self::assertSame('site-title', PhotoFilename::of('Site', 'Title', null, 1, null));
    }
}
