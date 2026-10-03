<?php

namespace Base\Press\Tests\Entity;

use Base\Press\Entity\Bio;
use Base\Press\Enum\BioLength;
use PHPUnit\Framework\TestCase;

/** A biography: its words counted the way a programme editor counts them, its paragraphs, its length held. */
final class BioTest extends TestCase
{
    public function testWordsAreWhatStandsBetweenTwoBlanks(): void
    {
        self::assertSame(0, Bio::countWords(''));
        self::assertSame(0, Bio::countWords(" \n – — \n"));
        self::assertSame(5, Bio::countWords('Clara Weiss plays the harp.'));
        // An apostrophe, a hyphen: one word each.
        self::assertSame(4, Bio::countWords("L'archet de l'élève brille"));
        self::assertSame(2, Bio::countWords('NDR Sinfonie-Orchester'));
        // Accents and other alphabets count like any letter.
        self::assertSame(3, Bio::countWords('Née à Besançon'));
        self::assertSame(2, Bio::countWords('Дмитрий Шостакович'));
        // A year, an opus number.
        self::assertSame(5, Bio::countWords('Born in 1994, op. 12'));
    }

    public function testTheCountOfABiographyIsThatOfItsContent(): void
    {
        $bio = new Bio('en', BioLength::SHORT, "Clara Weiss plays the harp.\n\nShe lives in Basel.");

        self::assertSame(9, $bio->getWordCount());
        self::assertSame(0, (new Bio())->getWordCount());
    }

    public function testParagraphsAreWhatABlankLineSeparates(): void
    {
        $bio = new Bio('en', BioLength::MEDIUM, "First paragraph,\nstill the first.\r\n\r\nSecond one.\n\n\n\nThird.  ");

        self::assertSame(["First paragraph,\nstill the first.", 'Second one.', 'Third.'], $bio->getParagraphs());
        self::assertSame([], (new Bio())->getParagraphs());
    }

    public function testTheContentIsTrimmedAndAnEmptyOneIsNothing(): void
    {
        self::assertSame('Text.', (new Bio())->setContent("  Text.\r\n ")->getContent());
        self::assertNull((new Bio())->setContent("  \n ")->getContent());
    }

    public function testAShortBiographyIsTooLongPastAHundredAndTenWords(): void
    {
        $bio = new Bio('en', BioLength::SHORT, trim(str_repeat('word ', 110)));
        self::assertFalse($bio->isTooLong());

        $bio->setContent(trim(str_repeat('word ', 111)));
        self::assertTrue($bio->isTooLong());

        // The medium one holds 250, the full one whatever it needs.
        self::assertFalse($bio->setLength(BioLength::MEDIUM)->isTooLong());
        self::assertFalse($bio->setLength('long')->setContent(trim(str_repeat('word ', 5000)))->isTooLong());
    }

    public function testEachLengthSaysTheWordsItHolds(): void
    {
        self::assertSame(100, BioLength::SHORT->words());
        self::assertSame(250, BioLength::MEDIUM->words());
        self::assertNull(BioLength::LONG->words());
    }

    public function testTheLocaleIsLowercasedAndAnUnknownLengthIsShort(): void
    {
        $bio = (new Bio('FR'))->setLength('nonsense');

        self::assertSame('fr', $bio->getLocale());
        self::assertSame(BioLength::SHORT, $bio->getLength());
        self::assertSame('short · fr', (string) $bio);
    }
}
