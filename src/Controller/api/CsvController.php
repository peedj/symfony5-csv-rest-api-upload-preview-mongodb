<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\CsvFile;
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
     */
    public function postCsvFile(Request $request): View
    {
        $file = $request->files->get('csv_file');

        /** @var $file UploadedFile */
        $filePath = $this->documentManager->getRepository(CsvFile::class)->saveCsvFile($file);

        $csvFile = new CsvFile();
        $csvFile->setFileName($file->getClientOriginalName());
        $csvFile->setFilePath($filePath);
        $csvFile->setStatus(CsvFile::STATUS_NEW);
        $csvFile->setDateCreated(new \MongoDB\BSON\Timestamp(1, date('U')));

        $this->documentManager->persist($csvFile);
        $this->documentManager->flush();

        $process = new Process(['php', 'bin/console', 'app:parse-csv']);
        $process->start();

        return View::create($csvFile->getId(), Response::HTTP_CREATED);
    }

    /**
     * @Rest\Get("/csv/{id}")
     */
    public function getCsvFile(int $id): View
    {
        $csvFile = $this->objectRepository->find($id);

        return View::create($csvFile, Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/csv")
     */
    public function getCsvFiles(): View
    {
        $csvFiles = $this->documentManager->getRepository(CsvFile::class)->findAll();
        $serializer = new Serializer([new ObjectNormalizer()]);
        return View::create($serializer->normalize($csvFiles), Response::HTTP_OK);
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