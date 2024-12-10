<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findTop5MostSoldProducts(): array
    {
        $results = $this->createQueryBuilder('p')
            ->select('p as product, COALESCE(SUM(op.amount), 0) as totalSold, p.image as image, p.id as id')
            ->leftJoin('App\Entity\OrderProduct', 'op', 'WITH', 'op.product = p.id')
            ->groupBy('p.id')
            ->orderBy('totalSold', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // If there are fewer than 5, add placeholders
        if (count($results) < 5) {
            $placeholders = 5 - count($results);
            for ($i = 0; $i < $placeholders; $i++) {
                $results[] = [
                    'product' => null, // Placeholder product
                    'totalSold' => 0,
                    'image' => 'placeholder.jpg', // Placeholder image
                    'id' => null,
                ];
            }
        }
        return $results;
    }


    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
