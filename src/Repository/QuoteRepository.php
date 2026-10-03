<?php

namespace Base\Press\Repository;

use Base\Press\Entity\Quote;
use Base\Press\Enum\QuoteKind;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Quote> */
class QuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

    /**
     * The quotes shown to a reader of $locale - written in it, or in no
     * language in particular -, the featured ones first, then in their order,
     * the newest first among equals. No locale: every language.
     *
     * @return list<Quote>
     */
    public function findVisible(?string $locale = null, ?QuoteKind $kind = null, ?int $limit = null, bool $featuredOnly = false): array
    {
        $query = $this->createQueryBuilder('q')
            ->andWhere('q.visible = true')
            ->orderBy('q.featured', 'DESC')->addOrderBy('q.position', 'ASC')->addOrderBy('q.date', 'DESC')->addOrderBy('q.id', 'DESC');
        if ($locale) {
            $query->andWhere('q.locale = :locale OR q.locale IS NULL')->setParameter('locale', mb_strtolower(substr($locale, 0, 2)));
        }
        if ($kind) {
            $query->andWhere('q.kind = :kind')->setParameter('kind', $kind);
        }
        if ($featuredOnly) {
            $query->andWhere('q.featured = true');
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * The same, falling back on another language (English) when the reader's
     * has nothing: a German page with no German quote shows the English ones
     * rather than none.
     *
     * @return list<Quote>
     */
    public function findForLocale(string $locale, string $fallback = 'en', ?QuoteKind $kind = null, ?int $limit = null, bool $featuredOnly = false): array
    {
        $quotes = $this->findVisible($locale, $kind, $limit, $featuredOnly);
        if (!$quotes && mb_strtolower(substr($locale, 0, 2)) !== $fallback) {
            $quotes = $this->findVisible($fallback, $kind, $limit, $featuredOnly);
        }

        return $quotes;
    }
}
