<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Quote;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Quote>
 */
class QuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

    //    /**
    //     * @return Quote[] Returns an array of Quote objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    /**
    //     * @return Quote[] Returns an array of Quotes objects
    //     */
    // Requête pour récupérer les RDV user
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('q')
            ->innerJoin('q.appointments', 'a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('q.quoteDate', 'DESC') // Trié par date de début
            ->getQuery()
            ->getResult()
        ;
    }

    //    public function findOneBySomeField($value): ?Quote
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
