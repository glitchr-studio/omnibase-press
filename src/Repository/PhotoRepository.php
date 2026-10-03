<?php

namespace Base\Press\Repository;

use Base\Press\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Photo> */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    /** @return list<Photo> the pictures shown, in their order */
    public function findVisible(?int $limit = null): array
    {
        return $this->findBy(['visible' => true], ['position' => 'ASC', 'id' => 'ASC'], $limit);
    }

    /** @return list<Photo> the pictures of the site's own gallery, in their order */
    public function findGallery(?int $limit = null): array
    {
        return $this->findBy(['gallery' => true], ['position' => 'ASC', 'id' => 'ASC'], $limit);
    }
}
