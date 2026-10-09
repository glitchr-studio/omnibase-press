<?php

namespace Base\Press\Entity;

use Base\Press\Enum\QuoteKind;
use Base\Press\Repository\QuoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * What was said or written: the words, who said them ("Joshua Bell",
 * "violinist"), where ("BBC Music Magazine", with its link and date), and
 * what about (the album, the concert - a free reference, no relation).
 *
 * The words are kept as they were said or written (`language` says in which
 * tongue), with their translations beside them (`translations`: per
 * language, the words and the speaker's quality): a reader sees the words in
 * their own language when there is a translation, and may ask for the
 * original (`offerOriginal`). `locale` only restricts a quote to the readers
 * of one language (empty: shown to all).
 */
#[ORM\Entity(repositoryClass: QuoteRepository::class)]
#[ORM\Table(name: 'press_quote')]
#[ORM\Index(columns: ['visible', 'featured', 'position'], name: 'press_quote_shown_idx')]
class Quote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    protected ?string $text = null;

    /** The language of the text; null: shown in every language. */
    #[ORM\Column(length: 5, nullable: true)]
    #[Assert\Length(max: 5)]
    protected ?string $locale = null;

    /** The language the words were said or written in ("en"): what "the original" is, and its lang attribute. */
    #[ORM\Column(length: 5, nullable: true)]
    #[Assert\Length(max: 5)]
    protected ?string $language = null;

    /** @var array<string, array{text?: string, role?: string}>|null per language, the words translated and the speaker's quality */
    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $translations = null;

    /** Whether a translated quote lets the reader see the original words. */
    #[ORM\Column(name: 'offer_original', type: 'boolean', options: ['default' => true])]
    protected bool $offerOriginal = true;

    /** Who speaks: a critic, a colleague. Empty when only the paper signs. */
    #[ORM\Column(length: 160, nullable: true)]
    #[Assert\Length(max: 160)]
    protected ?string $author = null;

    /** What the author is: "violinist", "conductor", "BBC Music Magazine". */
    #[ORM\Column(length: 160, nullable: true)]
    #[Assert\Length(max: 160)]
    protected ?string $role = null;

    /** The publication. */
    #[ORM\Column(length: 160, nullable: true)]
    #[Assert\Length(max: 160)]
    protected ?string $source = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(requireTld: true)]
    #[Assert\Length(max: 500)]
    protected ?string $url = null;

    #[ORM\Column(name: 'quoted_on', type: 'date_immutable', nullable: true)]
    protected ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: 'string', length: 16, enumType: QuoteKind::class)]
    protected QuoteKind $kind = QuoteKind::PRESS;

    /** Among the few a home page shows (press_quotes()). */
    #[ORM\Column(type: 'boolean')]
    protected bool $featured = false;

    #[ORM\Column(type: 'integer')]
    protected int $position = 0;

    /** What it is about - an album's title, a concert: free words, no relation. */
    #[ORM\Column(name: 'about_release', length: 200, nullable: true)]
    #[Assert\Length(max: 200)]
    protected ?string $release = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $visible = true;

    public function __construct(?string $text = null, ?string $author = null, ?string $source = null)
    {
        $this->setText($text);
        $this->setAuthor($author);
        $this->setSource($source);
    }

    public function __toString(): string
    {
        return mb_strimwidth((string) $this->text, 0, 60, '…');
    }

    public function getId(): ?int { return $this->id; }

    public function getText(): ?string { return $this->text; }
    public function setText(?string $text): self
    {
        // The quotation marks are the template's: the ones typed around the text go.
        $text = null !== $text ? trim($text) : '';
        $text = preg_replace('/^["“”«»„\s]+|["“”«»\s]+$/u', '', $text);
        $this->text = '' !== $text ? $text : null;

        return $this;
    }

    public function getLocale(): ?string { return $this->locale; }
    public function setLocale(?string $locale): self { $this->locale = $locale ? mb_strtolower(trim($locale)) : null; return $this; }

    public function getAuthor(): ?string { return $this->author; }
    public function setAuthor(?string $author): self { $this->author = self::clean($author); return $this; }

    public function getRole(): ?string { return $this->role; }
    public function setRole(?string $role): self { $this->role = self::clean($role); return $this; }

    public function getSource(): ?string { return $this->source; }
    public function setSource(?string $source): self { $this->source = self::clean($source); return $this; }

    public function getUrl(): ?string { return $this->url; }
    public function setUrl(?string $url): self { $this->url = self::clean($url); return $this; }

    public function getDate(): ?\DateTimeImmutable { return $this->date; }
    public function setDate(?\DateTimeInterface $date): self { $this->date = $date ? \DateTimeImmutable::createFromInterface($date) : null; return $this; }

    public function getKind(): QuoteKind { return $this->kind; }
    public function setKind(QuoteKind|string $kind): self { $this->kind = $kind instanceof QuoteKind ? $kind : (QuoteKind::tryFrom($kind) ?? QuoteKind::PRESS); return $this; }

    public function isFeatured(): bool { return $this->featured; }
    public function setFeatured(bool $featured): self { $this->featured = $featured; return $this; }
    /** The back office's button: featured, or no longer. */
    public function toggleFeatured(): self { $this->featured = !$this->featured; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(?int $position): self { $this->position = (int) $position; return $this; }

    public function getRelease(): ?string { return $this->release; }
    public function setRelease(?string $release): self { $this->release = self::clean($release); return $this; }

    public function isVisible(): bool { return $this->visible; }
    public function setVisible(bool $visible): self { $this->visible = $visible; return $this; }

    public function getLanguage(): ?string { return $this->language ?? $this->locale; }
    public function setLanguage(?string $language): self { $this->language = $language ? mb_strtolower(substr(trim($language), 0, 5)) : null; return $this; }

    public function isOfferOriginal(): bool { return $this->offerOriginal; }
    public function setOfferOriginal(bool $offer): self { $this->offerOriginal = $offer; return $this; }

    /** @return array<string, array{text?: string, role?: string}> */
    public function getTranslations(): array { return $this->translations ?? []; }

    /** @param array<string, array{text?: string, role?: string}|string>|null $translations a bare string is the words */
    public function setTranslations(?array $translations): self
    {
        $this->translations = null;
        foreach ($translations ?? [] as $locale => $row) {
            $row = \is_array($row) ? $row : ['text' => $row];
            $this->setTranslation((string) $locale, $row['text'] ?? null, $row['role'] ?? null);
        }

        return $this;
    }

    /** The words (and, when given, the speaker's quality) in one language; nothing of either removes it. */
    public function setTranslation(string $locale, ?string $text, ?string $role = null): self
    {
        $locale = mb_strtolower(substr(trim($locale), 0, 2));
        $text = preg_replace('/^["“”«»„\s]+|["“”«»\s]+$/u', '', trim((string) $text));
        $row = array_filter(['text' => $text, 'role' => self::clean($role)], static fn ($v) => null !== $v && '' !== $v);
        $all = $this->translations ?? [];
        if ($row) {
            $all[$locale] = $row;
        } else {
            unset($all[$locale]);
        }
        $this->translations = $all ?: null;

        return $this;
    }

    private function translation(string $locale): array
    {
        return $this->translations[mb_strtolower(substr($locale, 0, 2))] ?? [];
    }

    /** Whether a reader of $locale reads a translation (there is one, and it is not the original's language). */
    public function isTranslatedIn(string $locale): bool
    {
        return isset($this->translation($locale)['text']) && mb_strtolower(substr($locale, 0, 2)) !== $this->getLanguage();
    }

    /** The words for a reader of $locale: the translation, else the original. */
    public function getTextIn(string $locale): ?string
    {
        return $this->isTranslatedIn($locale) ? $this->translation($locale)['text'] : $this->text;
    }

    /** The speaker's quality for a reader of $locale ("chef d'orchestre"), else as typed. */
    public function getRoleIn(string $locale): ?string
    {
        return $this->translation($locale)['role'] ?? $this->role;
    }

    /**
     * The back office's fields, one pair per language of the site:
     * translation_fr (the words) and role_fr (the quality) read and write
     * `translations`.
     */
    public function __isset(string $name): bool { return 1 === preg_match('/^(translation|role)_[a-z]{2}$/', $name); }

    public function __get(string $name): ?string
    {
        if (!preg_match('/^(translation|role)_([a-z]{2})$/', $name, $m)) {
            throw new \LogicException(sprintf('No property "%s" on a quote.', $name));
        }

        return $this->translation($m[2])['translation' === $m[1] ? 'text' : 'role'] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        if (!preg_match('/^(translation|role)_([a-z]{2})$/', $name, $m)) {
            throw new \LogicException(sprintf('No property "%s" on a quote.', $name));
        }
        $now = $this->translation($m[2]);
        'translation' === $m[1]
            ? $this->setTranslation($m[2], null !== $value ? (string) $value : null, $now['role'] ?? null)
            : $this->setTranslation($m[2], $now['text'] ?? null, null !== $value ? (string) $value : null);
    }

    /** Shown to a reader of $locale: written in it, or in no language in particular. */
    public function speaks(string $locale): bool
    {
        return null === $this->locale || $this->locale === mb_strtolower(substr($locale, 0, 2)) || $this->locale === mb_strtolower($locale);
    }

    /**
     * The line under the words: "Joshua Bell, violinist" or, with only the
     * paper, "BBC Music Magazine". The source is not repeated when the role
     * already names it.
     */
    public function getSignature(): string
    {
        $who = implode(', ', array_filter([$this->author, $this->role]));
        $source = $this->source && $this->source !== $this->role && $this->source !== $this->author ? $this->source : null;

        return implode(' — ', array_filter([$who, $source]));
    }

    private static function clean(?string $value): ?string
    {
        $value = null !== $value ? trim($value) : '';

        return '' !== $value ? $value : null;
    }
}
