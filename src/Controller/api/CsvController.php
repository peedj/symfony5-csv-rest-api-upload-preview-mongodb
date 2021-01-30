<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\File;
use App\Entity\FileData;
use App\Repository\FileDataRepository;
use App\Repository\FileRepository;
use Bilendi\DevExpressBundle\DataGrid\Parser\SearchQueryParser;
use Bilendi\DevExpressBundle\DataGrid\QueryHandler\DoctrineQueryConfig;
use Bilendi\DevExpressBundle\DataGrid\QueryHandler\DoctrineQueryHandler;
use Bilendi\DevExpressBundle\DataGrid\Search\SearchQuery;
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
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class CsvController
    extends AbstractFOSRestController
{
    /** @var FileRepository */
    private FileRepository $fileRepository;
    /** @var FileDataRepository */
    private FileDataRepository $fileDataRepository;


    /**
     * CsvController constructor.
     * @param FileRepository $fileRepository
     * @param FileDataRepository $fileDataRepository
     */
    public function __construct(FileRepository $fileRepository, FileDataRepository $fileDataRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->fileDataRepository = $fileDataRepository;
    }

    /**
     * @Rest\Post("/csv")
     * @param Request $request
     * @return View
     * @throws ExceptionInterface
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

        $savePath = $this->fileRepository->saveCsvFile($this->getParameter('env(UPLOAD_FOLDER)'), $file);

        $newFile = File::create($file->getClientOriginalName(), $savePath);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($newFile);
        $entityManager->flush();

        /* @todo: Refactor to flock or RabbitMQ
         * $process = new Process(['bin/console', 'app:parse-csv', '> /dev/null 2>&1 &'], __DIR__ . "/../../../");
         * $process->setOptions(['create_new_console' => true]);
         * $process->run();
         */

        $path = __DIR__ . "/../../../";
        `cd $path && (bin/console app:parse-csv > /dev/null 2>&1 &)`;

        $serializer = new Serializer([new ObjectNormalizer()]);
        return View::create($serializer->normalize($newFile), Response::HTTP_CREATED);
    }

    /**
     * @Rest\Get("/csv/{fileId}")
     * @param int $fileId
     * @param Request $request
     * @return View
     * @throws ExceptionInterface
     */
    public function getCsvFile(int $fileId, Request $request): View
    {
        $csvFile = $this->fileRepository->findOneBy(['id' => $fileId]);
        if(!$csvFile) {
            throw new NotFoundHttpException('File Not Found');
        }

        $serializer = new Serializer([new ObjectNormalizer()]);
        return View::create($serializer->normalize($csvFile, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['fileData']]), Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/csv-stats/{fileId}")
     * @param int $fileId
     * @param Request $request
     * @param int|null $page
     * @param int|null $perPage
     * @return View
     * @throws ExceptionInterface
     * @throws \Bilendi\DevExpressBundle\Exception\NotNumericException
     */
    public function getCsvFileStats(int $fileId, Request $request): View
    {
        $csvFile = $this->fileRepository->findOneBy(['id' => $fileId]);
        if(!$csvFile) {
            throw new NotFoundHttpException('File Not Found');
        }

        // Initiate the parser
        $parser = new SearchQueryParser();

        $page = $request->get('page', 1);
        $perPage = min(50, $request->get('perPage', 10));

        $skip = ($page - 1) * $perPage;
        $parser->getBuilder()->setStartIndex($skip);
        $parser->getBuilder()->setMaxResults($perPage);

        if($loadOptions = $request->get('loadOptions')) {
            // Parse the DevExpress object
            $query = $parser->parse(json_decode($loadOptions));
        } else {
            $query = $parser->getBuilder()->build();
        }

        // Link between the column header and the doctrine field
        $map = [
            'client' => 'fd.client',
            'group_month' => 'DATE_FORMAT(fd.date, \'%Y-%m\')',
        ];
        // Create the config with the mapping
        $config = new DoctrineQueryConfig($map);
        $serializer = new Serializer([new DateTimeNormalizer(), new ObjectNormalizer()]);
        // Return the data and the total number of item
        return View::create([
            'fileName' => $csvFile->getFileName(),
            'items' => $serializer->normalize($this->getContent($fileId, $config, $query),  'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['file']]),
            'totalCount' => $this->getTotal($fileId, $config, $query),
        ], Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/csv/{page}/{perPage}")
     * @param int $perPage
     * @param int $page
     * @return View
     * @throws ExceptionInterface
     */
    public function getCsvFiles(int $page = 1, int $perPage = 10): View
    {
        $skip = $perPage * ($page - 1);
        $csvFiles = $this->fileRepository->getFileList($perPage, $skip);

        $serializer = new Serializer([new ObjectNormalizer()]);

        return View::create([
            'items' => $serializer->normalize($csvFiles,  'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['fileData']]),
            'atall' => $this->fileRepository->getAtAll(),
        ], Response::HTTP_OK);
    }


    /**
     * @Rest\Delete("/csv/{id}", name="delete", requirements={"id":"\d+"})
     * @param Request $request
     * @return View
     */
    public function deleteCsvFile(Request $request): View
    {
        $id = $request->get('id');
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($this->fileRepository->findOneBy(['id' => $id]));
        $entityManager->flush();

        return View::create(Response::HTTP_OK);
    }

    /**
     * @param int|null $fileId
     * @param DoctrineQueryConfig $config
     * @param SearchQuery $query
     * @return int|mixed|string
     */
    private function getContent(?int $fileId = null, DoctrineQueryConfig $config, SearchQuery $query)
    {
        // Create the query builder
        $queryBuilder = $this->fileDataRepository->getBaseStatsQuery($fileId);

        // Create the query handle
        $handler = new DoctrineQueryHandler($config, $queryBuilder, $query);
        // Binds the filters, pagination and sorting
        $queryBuilder = $handler->addAllModifiers();
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param int|null $fileId
     * @param DoctrineQueryConfig $config
     * @param SearchQuery $query
     * @return mixed
     */
    private function getTotal(?int $fileId = null, DoctrineQueryConfig $config, SearchQuery $query)
    {
        $queryBuilder = $this->fileDataRepository->getBaseStatsQuery($fileId);

        $handler = new DoctrineQueryHandler($config, $queryBuilder, $query);
        // Add only the filters. You must not add the pagination. You should not add sorting (useless for counting)
        $handler->addFilters();

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($queryBuilder->getQuery());
        return count($paginator);
    }
}