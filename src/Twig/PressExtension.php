<?php

namespace Base\Press\Twig;

use Base\Press\Entity\Bio;
use Base\Press\Enum\BioLength;
use Base\Press\Enum\QuoteKind;
use Base\Press\Repository\BioRepository;
use Base\Press\Repository\PhotoRepository;
use Base\Press\Repository\QuoteRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * What a host's own pages ask the press kit: a few quotes for a home page
 * (the featured ones first), the biography of one length for an "about"
 * page, the press kit's pictures, the site's gallery, who to write to (press.contacts) for a
 * contact page.
 */
final class PressExtension extends AbstractExtension
{
    public function __construct(
        private readonly QuoteRepository $quotes,
        private readonly BioRepository $bios,
        private readonly PhotoRepository $photos,
        private readonly RequestStack $requests,
        #[Autowire('%press.contacts%')] private readonly array $contacts = [],
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('press_quotes', fn (int $limit = 3, ?string $kind = null): array => $this->quotes->findForLocale($this->locale(), 'en', $kind ? QuoteKind::tryFrom($kind) : null, $limit)),
            new TwigFunction('press_bio', fn (string $length = 'short', ?string $locale = null): ?Bio => $this->bios->findOneFor(BioLength::tryFrom($length) ?? BioLength::SHORT, $locale ?? $this->locale())),
            new TwigFunction('press_photos', fn (?int $limit = null): array => $this->photos->findVisible($limit)),
            new TwigFunction('press_gallery', fn (?int $limit = null): array => $this->photos->findGallery($limit)),
            new TwigFunction('press_contacts', fn (): array => $this->contacts),
        ];
    }

    private function locale(): string
    {
        return $this->requests->getCurrentRequest()?->getLocale() ?? 'en';
    }
}
