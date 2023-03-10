<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Task[] Returns an array of Task objects
     */
    public function findByUser(array $user, int $isdone): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.user in (:user)')
            ->andWhere('t.isDone = :isdone')
            ->setParameters([
                'user' => $user,
                'isdone' => $isdone
            ])
            ->orderBy('t.deadline', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Task[] Returns an array of Task objects
    //  */
    // public function findExpired(): array
    // {
    //     $date = new \DateTimeImmutable();
    //     return $this->createQueryBuilder('t')
    //         ->where('t.deadline < :date')
    //         ->andWhere('t.isDone = :isDone')
    //         ->setParameters([
    //             'date' => $date,
    //             'isDone' => 0
    //         ])
    //         ->getQuery()
    //         ->getResult();
    // }
}
