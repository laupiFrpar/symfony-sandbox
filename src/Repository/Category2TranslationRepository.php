<?php

namespace Lopi\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lopi\Entity\Category2;
use Lopi\Entity\Category2Translation;

/**
 * @method Category2Translation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category2Translation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category2Translation[]    findAll()
 * @method Category2Translation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Category2TranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category2Translation::class);
    }

    /**
     * @param Category2 $category
     * @param string    $locale
     *
     * @return Category2Translation|null
     */
    public function findOneByCategoryAndLocale(Category2 $category, string $locale): ?Category2Translation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.translatable = :translatable')
            ->andWhere('c.locale = :locale')
            ->setParameter('translatable', $category)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
