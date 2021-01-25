<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\CsvFile;
use App\Document\CsvFileData;
use App\Repository\CsvFileRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class CsvController
    extends AbstractFOSRestController
{
    /** @var DocumentManager */
    private $documentManager;

    /** @var CsvFileRepository */
    private $objectRepository;

    public function __construct(DocumentManager $dm)
    {
        $this->documentManager = $dm;
    }

    /**
     * @Rest\Post("/csv")
     * @param Request $request
     * @return View
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function postCsvFile(Request $request): View
    {
        /** @var $file UploadedFile */
        $file = $request->files->get('csv_file');


        if (!$file) {
            throw new \Exception('CSV File Missing');
        }

        if ($file->getClientOriginalExtension() != "csv") {
            throw new \Exception('Only CSV Files Allowed');
        }

        $filePath = $this->documentManager->getRepository(CsvFile::class)->saveCsvFile($file);

        $csvFile = new CsvFile();
        $csvFile->setFileName($file->getClientOriginalName());
        $csvFile->setFilePath($filePath);
        $csvFile->setStatus(CsvFile::STATUS_NEW);
        $csvFile->setDateCreated(new \MongoDB\BSON\Timestamp(1, date('U')));

        $this->documentManager->persist($csvFile);
        $this->documentManager->flush();

        $process = new Process(['bin/console', 'app:parse-csv'], getcwd() . "/../");
        $process->setOptions(['create_new_console' => true]);
        $process->run();

        $serializer = new Serializer([new ObjectNormalizer()]);
        return View::create($serializer->normalize($csvFile), Response::HTTP_CREATED);
    }

    /**
     * @Rest\Get("/csv-data/{csvFileId}/{page}/{perPage}")
     * @param int $csvFileId
     * @param int $page
     * @param int $perPage
     * @return View
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     */
    public function getCsvFileData(string $csvFileId, int $page = 1, int $perPage = 10): View
    {
        /**
         * @var $csvFile CsvFile
         */
        $csvFile = $this->documentManager->getRepository(CsvFile::class)->findOneBy(['id' => $csvFileId]);

        if (!$csvFile) {
            throw new NotFoundHttpException();
        }

        $skip = $perPage * ($page - 1);
        $csvFileData = $this->documentManager->createQueryBuilder(CsvFileData::class)
            ->field('csvFileId')->equals($csvFileId)
            ->limit($perPage)
            ->skip($skip)
            ->getQuery()
            ->execute();

        $atall = $this->documentManager->createQueryBuilder(CsvFileData::class)
            ->field('csvFileId')->equals($csvFileId)->count()->getQuery()->execute();

        $serializer = new Serializer([new ObjectNormalizer()]);

        return View::create([
            'fileName' => $csvFile->getFileName(),
            'items' => array_map(function ($cfd) {
                return $cfd['data'];
            }, $csvFileData ? $serializer->normalize($csvFileData) : []),
            'atall' => $atall,
        ], Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/csv/{page}/{perPage}")
     * @param int $perPage
     * @param int $page
     * @return View
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getCsvFiles(int $page = 1, int $perPage = 10): View
    {

        $skip = $perPage * ($page - 1);
        $csvFiles = $this->documentManager->createQueryBuilder(CsvFile::class)
            ->limit($perPage)
            ->skip($skip)
            ->sort('date_created', 'desc')
            ->getQuery()
            ->execute();

        $atall = $this->documentManager->createQueryBuilder(CsvFile::class)->count()->getQuery()->execute();

        $serializer = new Serializer([new ObjectNormalizer()]);

        return View::create([
            'items' => $serializer->normalize($csvFiles),
            'atall' => $atall,
        ], Response::HTTP_OK);
    }

    /**
     * @Rest\Put("/csv/{id}")
     */
    public function putCsvFile(int $id, Request $request): View
    {
        $csv = $this->objectRepository->find($id);

        $csv->setFileName("?");

        $this->entityManager->persist($csv);
        $this->entityManager->flush();

        return View::create($csv, Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/csv")
     */
    public function deleteCsvFile(int $id): View
    {
        $this->entityManager->remove($this->objectRepository->find($id));
        $this->entityManager->flush();

        return View::create(Response::HTTP_OK);
    }
}