<?php

namespace App\Repository;

use App\Entity\CorporateParty;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CorporateParty|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorporateParty|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorporateParty[]    findAll()
 * @method CorporateParty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorporatePartyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CorporateParty::class);
    }

    public function findBetweenDates(DateTimeImmutable $dateStart, DateTimeImmutable $dateEnd)
    {
        return $this->createQueryBuilder('pr')
            ->andWhere('pr.date BETWEEN :begin AND :end')
            ->setParameter('begin', $dateStart->getTimestamp())
            ->setParameter('end', $dateEnd->getTimestamp())
            ->getQuery()
            ->getResult();
    }
}
