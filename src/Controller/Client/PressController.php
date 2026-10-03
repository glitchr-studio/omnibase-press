<?php

namespace Base\Press\Controller\Client;

use Base\Attributes\Attribute\Sitemap;
use Base\Press\Repository\BioRepository;
use Base\Press\Repository\PhotoRepository;
use Base\Press\Repository\QuoteRepository;
use Base\Press\Service\PhotoFilename;
use Base\Service\SettingBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The press page: what was written (the featured quotes), the biography in
 * the reader's language - each length with its "copy" button -, the pictures
 * with their credit and, when allowed, their original to download, and who
 * to write to.
 */
class PressController extends AbstractController
{
    /** The language shown when the reader's has nothing: the one every agency reads. */
    public const FALLBACK_LOCALE = 'en';

    public function __construct(
        private readonly QuoteRepository $quotes,
        private readonly BioRepository $bios,
        private readonly PhotoRepository $photos,
        private readonly SettingBagInterface $settings,
        #[Autowire('%press.contacts%')] private readonly array $contacts = [],
        #[Autowire('%press.download_kit%')] private readonly bool $downloadKit = true,
        #[Autowire('%press.bio_lengths%')] private readonly array $bioLengths = ['short', 'medium', 'long'],
    ) {
    }

    #[Sitemap(priority: 0.6, changefreq: 'monthly')]
    #[Route('/press', name: 'press_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $locale = $request->getLocale();
        // The featured quotes; a site that featured none shows them all.
        $quotes = $this->quotes->findForLocale($locale, self::FALLBACK_LOCALE, null, null, true)
            ?: $this->quotes->findForLocale($locale, self::FALLBACK_LOCALE);

        return $this->render('@Press/client/index.html.twig', [
            'quotes' => $quotes,
            'bios' => $this->bios->findByLength($locale, $this->bioLengths, self::FALLBACK_LOCALE),
            'photos' => $this->photos->findVisible(),
            'contacts' => $this->contacts,
            'download_kit' => $this->downloadKit,
        ]);
    }

    /** The original, as the photographer delivered it, under a name that says whose it is. */
    #[Route('/press/photo/{id}/download', name: 'press_photo_download', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function download(Request $request, int $id): Response
    {
        $photo = $this->photos->find($id);
        if (!$photo || !$photo->isVisible() || !$photo->isDownloadable() || !$this->downloadKit) {
            throw $this->createNotFoundException(sprintf('No picture to download at "%d".', $id));
        }
        $file = $photo->getFileFile() ?? throw $this->createNotFoundException('The picture has no file.');

        $name = PhotoFilename::of($this->siteName($request), $photo->getTitle(), $photo->getCredit(), $photo->getId(), $file->guessExtension() ?: $file->getExtension());
        $response = $this->file($file, $name, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        // A robot has the page; the originals are for people.
        $response->headers->set('X-Robots-Tag', 'noindex');

        return $response;
    }

    /** The site's title (base.settings.title), else its host. */
    private function siteName(Request $request): string
    {
        try {
            $title = $this->settings->getScalar('base.settings.title');
        } catch (\Throwable) {
            $title = null;
        }

        return \is_string($title) && '' !== trim($title) ? $title : preg_replace('/^www\./', '', $request->getHost());
    }
}
