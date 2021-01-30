<?php

namespace App\Repository;

use App\Document\CsvFile;
use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function saveCsvFile(string $uploadDir, UploadedFile $file)
    {
        $filename = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
        $file->move(
            $uploadDir,
            $filename
        );

        return $uploadDir . "/" . $filename;
    }

    /**
     * @param int $perPage
     * @param int $skip
     * @return File[]|null
     */
    public function getFileList(int $perPage, int $skip): ?array
    {
        return $this->createQueryBuilder('f')
            ->setMaxResults($perPage)
            ->setFirstResult($skip)
            ->addOrderBy('f.created_at', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return int
     */
    public function getAtAll(): int
    {
        return $this->createQueryBuilder('f')
            ->select('count(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

}
