<?php

namespace App\Repository;

use App\Entity\Ingredient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ingredient>
 *
 * @method Ingredient|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ingredient|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ingredient[]    findAll()
 * @method Ingredient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }

    public function find_ingredient_tomate(): array
{
    return $this->createQueryBuilder('i')
        ->andWhere('i.nom = :val')
        ->setParameter('val', 'tomate')
        ->orderBy('i.id', 'ASC')
        ->getQuery()
        ->getResult(); // retourne un tableau d'objets Ingredient
}

public function find_ingredient_tomate_5(): array
{
    return $this->createQueryBuilder('i')
        ->andWhere('i.nom = :nom')
        ->andWhere('i.prix >= :prix')
        ->setParameter('nom', 'tomate')
        ->setParameter('prix', 5)
        ->orderBy('i.id', 'ASC')
        ->getQuery()
        ->getResult();
}

public function find_ingredient_tom(): array
{
    return $this->createQueryBuilder('i')
        ->andWhere('i.nom LIKE :val')
        ->setParameter('val', 'tom%')
        ->orderBy('i.id', 'ASC')
        ->getQuery()
        ->getResult();
}

public function find_ingredient_by_price(int $price): array
{
    return $this->createQueryBuilder('i')
        ->andWhere('i.prix = :prix')
        ->setParameter('prix', $price)
        ->orderBy('i.id', 'ASC')
        ->getQuery()
        ->getResult();
}

public function find_ingredient_by_price_and_name(int $price, string $name): array
{
    return $this->createQueryBuilder('i')
        ->andWhere('i.prix = :prix')
        ->andWhere('i.nom = :nom')
        ->setParameter('prix', $price)
        ->setParameter('nom', $name)
        ->orderBy('i.id', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findAll_sql(): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = 'SELECT * FROM ingredient';

    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery();

    return $resultSet->fetchAllAssociative(); // tableau de tableaux
}

public function findAll_dql(): array
{
    $em = $this->getEntityManager();

    $query = $em->createQuery('SELECT i FROM App\Entity\Ingredient i');

    return $query->getResult(); // objets Ingredient
}

public function find_ingredient_tomate_dql(): array
{
    $em = $this->getEntityManager();

    $query = $em->createQuery(
        'SELECT i FROM App\Entity\Ingredient i WHERE i.nom = :nom'
    )->setParameter('nom', 'tomate');

    return $query->getResult(); // retourne des objets Ingredient
}

public function find_ingredient_tomate_sql(): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = "SELECT * FROM ingredient WHERE nom = 'tomate'";
    return $conn->executeQuery($sql)->fetchAllAssociative();
}

public function find_ingredient_tomate_5_sql(): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = "SELECT * FROM ingredient WHERE nom = 'tomate' AND prix >= 5";
    return $conn->executeQuery($sql)->fetchAllAssociative();
}

public function find_ingredient_tom_sql(): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = "SELECT * FROM ingredient WHERE nom LIKE 'tom%'";
    return $conn->executeQuery($sql)->fetchAllAssociative();
}

public function find_ingredient_by_price_sql(int $price): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = "SELECT * FROM ingredient WHERE prix = :price";
    return $conn->executeQuery($sql, ['price' => $price])->fetchAllAssociative();
}

public function find_ingredient_by_price_and_name_sql(int $price, string $name): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = "SELECT * FROM ingredient WHERE prix = :price AND nom = :name";
    return $conn->executeQuery($sql, ['price' => $price, 'name' => $name])->fetchAllAssociative();
}

public function find_ingredient_tomate_5_dql(): array
{
    $em = $this->getEntityManager();
    $query = $em->createQuery("
        SELECT i FROM App\Entity\Ingredient i
        WHERE i.nom = 'tomate' AND i.prix >= 5
    ");
    return $query->getResult();
}

public function find_ingredient_tom_dql(): array
{
    $em = $this->getEntityManager();
    $query = $em->createQuery("
        SELECT i FROM App\Entity\Ingredient i
        WHERE i.nom LIKE 'tom%'
    ");
    return $query->getResult();
}

public function find_ingredient_by_price_dql(int $price): array
{
    $em = $this->getEntityManager();
    $query = $em->createQuery("
        SELECT i FROM App\Entity\Ingredient i
        WHERE i.prix = :price
    ")->setParameter('price', $price);

    return $query->getResult();
}

public function find_ingredient_by_price_and_name_dql(int $price, string $name): array
{
    $em = $this->getEntityManager();
    $query = $em->createQuery("
        SELECT i FROM App\Entity\Ingredient i
        WHERE i.prix = :price AND i.nom = :name
    ")
    ->setParameter('price', $price)
    ->setParameter('name', $name);

    return $query->getResult();
}


    //    /**
    //     * @return Ingredient[] Returns an array of Ingredient objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Ingredient
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
