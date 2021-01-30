<?php

namespace App\Command;

use App\Entity\File;
use App\Entity\FileData;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;

class ParseCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:parse-csv';
    private ObjectRepository $repository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $dm;


    public function __construct(string $name = null, EntityManagerInterface $em)
    {
        $this->dm = $em;
        date_default_timezone_set("Europe/Vilnius");
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('app:parse-csv')
            ->setDescription('Parses current New Uploaded Csv Files')
            ->setHelp('This command parses current New Uploaded Csv Files');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws MappingException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->repository = $this->dm->getRepository(File::class);
        $filesToProcess = $this->repository->findBy(['status' => File::STATUS_NEW]);
//        $filesToProcess = $this->repository->findAll();

        foreach ($filesToProcess as $fileToProcess) {
            /* @var $fileToProcess File */
            echo "parse: #{$fileToProcess->getId()} {$fileToProcess->getFileName()};\n";
            // update status to parse
            $fileToProcess->setStatus(File::STATUS_PARSE);
            $this->dm->flush();
            $this->dm->clear();
            // read from file
            try {
                $this->readFromFile($fileToProcess);
                // update status to done
                // you need to "re-find object"
                $this->repository->findOneBy(['id' => $fileToProcess->getId()])->setStatus(File::STATUS_COMPLETE);
                $this->unlinkFile($fileToProcess->getFilePath());
                $this->dm->flush();
                $this->dm->clear();
                echo "parse done: #{$fileToProcess->getId()} {$fileToProcess->getFileName()} : {$fileToProcess->getStatus()};\n";
            } catch (\Exception $e) {
                $this->repository->findOneBy(['id' => $fileToProcess->getId()])->setStatus(File::STATUS_FAIL);
                $this->dm->flush();
                $this->dm->clear();
                echo "parse error: #{$fileToProcess->getId()} {$fileToProcess->getFileName()} : {$fileToProcess->getStatus()};\n";
            }
        }
        return Command::SUCCESS;
    }

    /**
     *
     * Very fast read from file
     *
     * @param File $csvFile
     * @return int
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws MappingException
     */
    protected function readFromFile(File $csvFile): int
    {
        $delimeter = $this->detectDelimiter($csvFile->getFilePath());
        if (($handle = fopen($csvFile->getFilePath(), "r")) !== FALSE) {
            $this->readFromFileHandle($csvFile, $handle, $delimeter);
        }

        return -1;
    }

    /**
     * @param string $csvFile
     * @return false|int|string
     */
    public function detectDelimiter(string $csvFile)
    {
        $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

        $handle = fopen($csvFile, "r");
        $firstLine = fgets($handle);
        fclose($handle);
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }

    /**
     * @param File $file
     * @param $handle
     * @param string $delimiter
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws MappingException
     */
    protected function readFromFileHandle(
        File $file,
        $handle,
        string $delimiter
    ): void
    {
        $i = 0;
        $total = 0;
        $this->dm->getConnection()
        ->getConfiguration()
        ->setSQLLogger(null);
        while ($row = fgetcsv($handle, 0, $delimiter)) {
            if ($total > 0) {
                $date = \DateTime::createFromFormat('Y-m-d H:i', $row[0] . ' 00:00', new \DateTimeZone('Europe/Vilnius'));
                $newFileData = new FileData();
                $newFileData->setFile($this->dm->getReference(File::class, $file->getId()));
                $newFileData->setDate($date);
                $newFileData->setClient($row[1]);
                $newFileData->setSignSmartid($row[2]);
                $newFileData->setSignMobile($row[3]);
                $newFileData->setSignSc($row[4]);
                $newFileData->setAuthorizeSmartid($row[5]);
                $newFileData->setAuthorizeMobile($row[6]);
                $newFileData->setAuthorizeSc($row[7]);
                $newFileData->setOcsp($row[8]);
                $newFileData->setCrl($row[9]);

                $this->dm->persist($newFileData);
                $i++;
            }
            $total++;
            if ($i > 100) {
                $i = 0;
                $this->dm->flush();
                $this->dm->clear();
            }
        }
        $this->dm->flush();
        $this->dm->clear();
        echo "Imported lines: $total\n";
    }

    private function unlinkFile(?string $getFilePath)
    {
        echo 'delete '. $getFilePath . "\n";
        `rm -rf $getFilePath`;
    }
}