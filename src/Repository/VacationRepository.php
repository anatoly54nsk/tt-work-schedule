<?php

namespace App\Repository;

use App\Entity\Vacation;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Vacation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vacation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vacation[]    findAll()
 * @method Vacation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VacationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vacation::class);
    }

    public function findByStaffBetweenDates(int $staffId, DateTimeImmutable $dateStart, DateTimeImmutable $dateEnd)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.staff = :staff')
            ->setParameter('staff', $staffId)
            ->andWhere('v.start BETWEEN :begin AND :end')
            ->setParameter('begin', $dateStart->getTimestamp())
            ->setParameter('end', $dateEnd->getTimestamp())
            ->getQuery()
            ->getResult();
    }
}
