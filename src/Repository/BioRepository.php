<?php

namespace Base\Press\Repository;

use Base\Press\Entity\Bio;
use Base\Press\Enum\BioLength;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Bio> */
class BioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bio::class);
    }

    /** The biography of one length in $locale, else in the fallback language. */
    public function findOneFor(BioLength $length, string $locale, string $fallback = 'en'): ?Bio
    {
        $locale = mb_strtolower(substr($locale, 0, 2));

        return $this->findOneBy(['length' => $length, 'locale' => $locale])
            ?? ($locale !== $fallback ? $this->findOneBy(['length' => $length, 'locale' => $fallback]) : null);
    }

    /**
     * The biographies of a page, by length, each in $locale or else in the
     * fallback language; a length nobody wrote is left out.
     *
     * @param list<string> $lengths the lengths wanted, in order ('short', 'medium', 'long')
     *
     * @return array<string, Bio> length => biography
     */
    public function findByLength(string $locale, array $lengths = ['short', 'medium', 'long'], string $fallback = 'en'): array
    {
        $bios = [];
        foreach ($lengths as $length) {
            $length = BioLength::tryFrom($length);
            if ($length && $bio = $this->findOneFor($length, $locale, $fallback)) {
                $bios[$length->value] = $bio;
            }
        }

        return $bios;
    }
}
