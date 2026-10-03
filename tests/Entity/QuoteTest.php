<?php

namespace Base\Press\Tests\Entity;

use Base\Press\Entity\Quote;
use Base\Press\Enum\QuoteKind;
use PHPUnit\Framework\TestCase;

/** A quote: its words without the marks typed around them, who signs it, who it is shown to. */
final class QuoteTest extends TestCase
{
    public function testTheQuotationMarksTypedAroundTheTextGo(): void
    {
        self::assertSame('A sound of rare warmth.', (new Quote('“A sound of rare warmth.”'))->getText());
        self::assertSame('Un son d\'une rare chaleur', (new Quote('« Un son d\'une rare chaleur »'))->getText());
        self::assertSame('Ein Klang von seltener Wärme', (new Quote('„Ein Klang von seltener Wärme“'))->getText());
        self::assertSame('She said "yes" twice', (new Quote(' She said "yes" twice '))->getText());
        self::assertNull((new Quote('  '))->getText());
    }

    public function testADefaultQuoteIsAPressQuoteOnlineAndNotFeatured(): void
    {
        $quote = new Quote('Radiant.');

        self::assertSame(QuoteKind::PRESS, $quote->getKind());
        self::assertTrue($quote->isVisible());
        self::assertFalse($quote->isFeatured());
        self::assertSame(0, $quote->getPosition());
        self::assertNull($quote->getLocale());
    }

    public function testTheFeatureButtonTogglesIt(): void
    {
        $quote = new Quote('Radiant.');

        self::assertTrue($quote->toggleFeatured()->isFeatured());
        self::assertFalse($quote->toggleFeatured()->isFeatured());
    }

    public function testTheKindIsTakenFromItsValueAndAnUnknownOneIsPress(): void
    {
        $quote = new Quote('Radiant.');

        self::assertSame(QuoteKind::COLLEAGUE, $quote->setKind('colleague')->getKind());
        self::assertSame(QuoteKind::AUDIENCE, $quote->setKind(QuoteKind::AUDIENCE)->getKind());
        self::assertSame(QuoteKind::PRESS, $quote->setKind('nonsense')->getKind());
    }

    public function testTheSignatureNamesTheAuthorTheRoleAndTheSourceOnce(): void
    {
        $colleague = (new Quote('The most natural musician I know.', 'Joshua Bell'))->setRole('violinist');
        self::assertSame('Joshua Bell, violinist', $colleague->getSignature());

        $critic = (new Quote('Radiant.', 'Anna Picard', 'The Times'))->setRole('critic');
        self::assertSame('Anna Picard, critic — The Times', $critic->getSignature());

        // Only the paper signs: it is not said twice.
        $paper = (new Quote('Radiant.', null, 'BBC Music Magazine'))->setRole('BBC Music Magazine');
        self::assertSame('BBC Music Magazine', $paper->getSignature());

        self::assertSame('', (new Quote('Radiant.'))->getSignature());
    }

    public function testAQuoteWithNoLocaleSpeaksToEveryoneAndATranslatedOneToItsLanguage(): void
    {
        $everywhere = new Quote('Bravo!');
        self::assertTrue($everywhere->speaks('fr'));
        self::assertTrue($everywhere->speaks('de'));

        $german = (new Quote('Ein Ereignis.'))->setLocale('DE');
        self::assertSame('de', $german->getLocale());
        self::assertTrue($german->speaks('de'));
        self::assertTrue($german->speaks('de_AT'));
        self::assertFalse($german->speaks('en'));
    }

    public function testEmptyStringsAreKeptAsNothing(): void
    {
        $quote = (new Quote('Radiant.'))->setAuthor(' ')->setRole('')->setSource(null)->setUrl('  ')->setRelease('');

        self::assertNull($quote->getAuthor());
        self::assertNull($quote->getRole());
        self::assertNull($quote->getSource());
        self::assertNull($quote->getUrl());
        self::assertNull($quote->getRelease());
    }

    public function testTheDateIsKeptImmutable(): void
    {
        $date = new \DateTime('2026-03-14');
        $quote = (new Quote('Radiant.'))->setDate($date);
        $date->modify('+1 year');

        self::assertSame('2026-03-14', $quote->getDate()->format('Y-m-d'));
        self::assertNull($quote->setDate(null)->getDate());
    }

    public function testAsAStringItIsItsFirstWords(): void
    {
        self::assertSame('Radiant.', (string) new Quote('Radiant.'));
        self::assertSame(60, mb_strwidth((string) new Quote(str_repeat('word ', 40))));
    }
}
