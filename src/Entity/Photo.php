<?php

namespace Base\Press\Entity;

use Base\Database\Attribute\Uploader;
use Base\Press\Repository\PhotoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A picture of the artist: the file as the photographer delivered it (up to
 * 64 MB, a programme prints it), who took it - the credit a paper must print
 * with it -, a caption, and where it is shown: in the press kit (/press,
 * "visible"; its original downloadable or not), in the site's own gallery
 * ("gallery", press_gallery()), or both.
 */
#[ORM\Entity(repositoryClass: PhotoRepository::class)]
#[ORM\Table(name: 'press_photo')]
#[ORM\Index(columns: ['visible', 'position'], name: 'press_photo_shown_idx')]
class Photo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    /** The original: an upload. */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Uploader(max_size: '64MB', mime_types: ['image/*'])]
    protected $file = null;

    #[ORM\Column(length: 160, nullable: true)]
    #[Assert\Length(max: 160)]
    protected ?string $title = null;

    /** The photographer, as it must be printed: "© Harald Hoffmann". */
    #[ORM\Column(length: 160, nullable: true)]
    #[Assert\Length(max: 160)]
    protected ?string $credit = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $caption = null;

    /** The original may be taken (a picture cleared for the press); off, it is only shown. */
    #[ORM\Column(type: 'boolean')]
    protected bool $downloadable = true;

    #[ORM\Column(type: 'integer')]
    protected int $position = 0;

    /** In the press kit, on /press (with its "free for editorial use" note). */
    #[ORM\Column(type: 'boolean')]
    protected bool $visible = true;

    /** In the site's own gallery (press_gallery()): a picture the site shows, cleared for the press or not. */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $gallery = false;

    public function __toString(): string
    {
        return $this->title ?? $this->credit ?? '';
    }

    public function getId(): ?int { return $this->id; }

    public function getFile(): ?string { return Uploader::getPublic($this, 'file'); }
    public function getFileFile(): ?File { return Uploader::get($this, 'file'); }
    public function setFile($file): self { $this->file = $file; return $this; }
    /** Whether a picture is set, without asking the storage where it is. */
    public function hasFile(): bool { return null !== $this->file && '' !== $this->file; }

    /**
     * The picture's address on the site ("/uploads/…"), what an <img> takes:
     * the storage gives its public path from the server's root
     * ("/srv/app/public/uploads/…"), the public directory is taken off.
     */
    public function getFileUrl(): ?string
    {
        $path = $this->hasFile() ? $this->getFile() : null;
        if (!\is_string($path) || '' === $path || preg_match('#^(?:https?:)?//#i', $path)) {
            return $path ?: null;
        }
        $public = strpos($path, '/public/');

        return false !== $public ? substr($path, $public + \strlen('/public')) : $path;
    }

    /** What a screen reader says of the picture: its caption, else its title. */
    public function getAlt(): string
    {
        return $this->caption ?? $this->title ?? '';
    }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): self { $this->title = $title ? trim($title) : null; return $this; }

    public function getCredit(): ?string { return $this->credit; }
    public function setCredit(?string $credit): self { $this->credit = $credit ? trim($credit) : null; return $this; }

    /** The credit with its sign: "Harald Hoffmann" is printed "© Harald Hoffmann". */
    public function getCreditLine(): ?string
    {
        if (!$this->credit) {
            return null;
        }

        return preg_match('/^(©|\(c\)|photo\b|foto\b)/iu', $this->credit) ? $this->credit : '© '.$this->credit;
    }

    public function getCaption(): ?string { return $this->caption; }
    public function setCaption(?string $caption): self { $this->caption = $caption ? trim($caption) : null; return $this; }

    public function isDownloadable(): bool { return $this->downloadable; }
    public function setDownloadable(bool $downloadable): self { $this->downloadable = $downloadable; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(?int $position): self { $this->position = (int) $position; return $this; }

    public function isVisible(): bool { return $this->visible; }
    public function setVisible(bool $visible): self { $this->visible = $visible; return $this; }

    public function isGallery(): bool { return $this->gallery; }
    public function setGallery(bool $gallery): self { $this->gallery = $gallery; return $this; }
}
