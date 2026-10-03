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
 * One row is one language: the text and the locale it is in. A quote with
 * no locale is shown in every language (a line nobody would translate); a
 * translated one is a second row.
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
