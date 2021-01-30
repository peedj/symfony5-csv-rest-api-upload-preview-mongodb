<?php

namespace App\Repository;

use App\Entity\FileData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FileData|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileData|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileData[]    findAll()
 * @method FileData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileData::class);
    }

    /**
     * @param int|null $fileId
     * @return QueryBuilder
     */
    public function getBaseStatsQuery(?int $fileId = null): QueryBuilder
    {
        $query = $this->createQueryBuilder('fd');
        if ($fileId) {
            $query->select('DATE_FORMAT(fd.date, \'%Y-%m\') group_month, fd.id, fd.client, (SUM(fd.sign_smartid) + SUM(fd.sign_mobile) + SUM(fd.sign_sc)) as signs, (SUM(fd.authorize_smartid) + SUM(fd.authorize_mobile) + SUM(fd.authorize_sc)) as authorizations');
            $query->where('fd.file = ?1');
            $query->setParameter(1, $fileId);
            $query->groupBy('group_month');
            $query->addGroupBy('fd.client');
        }

        return $query;
    }
}
