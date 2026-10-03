<?php

namespace Base\Press\Entity;

use Base\Database\Attribute\Timestamp;
use Base\Press\Enum\BioLength;
use Base\Press\Repository\BioRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The biography, once per language and per length: the short one of a
 * season brochure, the medium one of a programme note, the full one. Plain
 * text, a blank line between two paragraphs - it is copied into a programme,
 * not laid out here. The date it was last touched is shown with it: an
 * agency wants to know the biography it prints is the current one.
 */
#[ORM\Entity(repositoryClass: BioRepository::class)]
#[ORM\Table(name: 'press_bio')]
#[ORM\UniqueConstraint(name: 'press_bio_locale_length_uniq', columns: ['locale', 'bio_length'])]
#[UniqueEntity(fields: ['locale', 'length'])]
class Bio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 5)]
    protected string $locale = 'en';

    #[ORM\Column(name: 'bio_length', type: 'string', length: 8, enumType: BioLength::class)]
    protected BioLength $length = BioLength::SHORT;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    protected ?string $content = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Timestamp(on: ['create', 'update'])]
    protected ?\DateTimeInterface $updatedAt = null;

    public function __construct(string $locale = 'en', BioLength $length = BioLength::SHORT, ?string $content = null)
    {
        $this->setLocale($locale);
        $this->length = $length;
        $this->setContent($content);
    }

    public function __toString(): string
    {
        return sprintf('%s · %s', $this->length->value, $this->locale);
    }

    public function getId(): ?int { return $this->id; }

    public function getLocale(): string { return $this->locale; }
    public function setLocale(?string $locale): self { $this->locale = mb_strtolower(trim((string) $locale)) ?: 'en'; return $this; }

    public function getLength(): BioLength { return $this->length; }
    public function setLength(BioLength|string $length): self { $this->length = $length instanceof BioLength ? $length : (BioLength::tryFrom($length) ?? BioLength::SHORT); return $this; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $content): self
    {
        // One line ending, no trailing blanks: what is copied is what was typed.
        $content = null !== $content ? trim(str_replace(["\r\n", "\r"], "\n", $content)) : '';
        $this->content = '' !== $content ? $content : null;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    /** @return list<string> the paragraphs: what a blank line separates */
    public function getParagraphs(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', (string) $this->content) ?: [])));
    }

    public function getWordCount(): int
    {
        return self::countWords((string) $this->content);
    }

    /** More words than its length is meant to hold (ten percent are forgiven); the full one never is. */
    public function isTooLong(): bool
    {
        $words = $this->length->words();

        // Integers: ceil(100 * 1.1) is 111 in floating point.
        return null !== $words && $this->getWordCount() > $words + intdiv($words, 10);
    }

    /**
     * The words of a text, the way a programme editor counts them: what
     * stands between two blanks, in any alphabet - "l'archet" and
     * "Sinfonie-Orchester" are one word each, a lone dash is none.
     */
    public static function countWords(string $text): int
    {
        return (int) preg_match_all('/[\p{L}\p{N}][\p{L}\p{N}\p{M}\'’\-.]*/u', $text);
    }
}
