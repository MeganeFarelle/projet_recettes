<?php

namespace App\Repository;

use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    /**
     * SQL : 10 premières recettes avec leurs ingrédients
     */
    public function find_recette_ingredient_sql(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                r.id AS recette_id,
                r.nom AS recette_nom,
                i.id AS ingredient_id,
                i.nom AS ingredient_nom
            FROM recette r
            INNER JOIN recette_ingredient ri ON r.id = ri.recette_id
            INNER JOIN ingredient i ON i.id = ri.ingredient_id
            ORDER BY r.id ASC
            LIMIT 10
        ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    /**
     * SQL : recettes qui ont exactement 5 ingrédients
     */
    public function find_recette_avec_5_ingredients_sql(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                r.*
            FROM recette r
            INNER JOIN recette_ingredient ri ON r.id = ri.recette_id
            GROUP BY r.id
            HAVING COUNT(ri.ingredient_id) = 5
        ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    /**
     * DQL : 10 premières recettes avec leurs ingrédients
     * Retourne des objets Recette (avec leurs ingrédients déjà chargés)
     */
    public function find_recette_ingredient_dql(): array
{
    // On ne joint PAS les ingrédients ici
    return $this->createQueryBuilder('r')
        ->orderBy('r.id', 'ASC')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();
}


    /**
     * DQL : recettes qui ont exactement 5 ingrédients
     */
    public function find_recette_avec_5_ingredients_dql(): array
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT r
             FROM App\Entity\Recette r
             JOIN r.ingredients i
             GROUP BY r.id
             HAVING COUNT(i.id) = 5'
        );

        return $query->getResult();
    }

    //    /**
    //     * @return Recette[] Returns an array of Recette objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Recette
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
