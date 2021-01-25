<?php

namespace App\Command;

use App\Document\CsvFile;
use App\Document\CsvFileData;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:parse-csv';
    private ObjectRepository $repository;
    /**
     * @var DocumentManager
     */
    private DocumentManager $dm;

    public function __construct(string $name = null, DocumentManager $dm)
    {
        $this->dm = $dm;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('app:parse-csv')
            ->setDescription('Parses current New Uploaded Csv Files')
            ->setHelp('This command parses current New Uploaded Csv Files');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->repository = $this->dm->getRepository(CsvFile::class);
//        $filesToProcess = $this->repository->findBy(['status' => CsvFile::STATUS_PARSE]);
        $filesToProcess = $this->repository->findAll();

        foreach ($filesToProcess as $fileToProcess) {
            $fileToProcess->setStatus(CsvFile::STATUS_PARSE);
            $this->dm->flush();
            $this->readFromFile($fileToProcess);
            $fileToProcess->setStatus(CsvFile::STATUS_COMPLETE);
            $this->dm->flush();
        }
        return Command::SUCCESS;
    }

    /**
     * Very fast read from file
     * @param CsvFile $csvFile
     * @return int
     */
    protected function readFromFile(CsvFile $csvFile): int
    {
        $delimeter = $this->detectDelimiter($csvFile->getFilePath());
        if (($handle = fopen($csvFile->getFilePath(), "r")) !== FALSE) {
            foreach($this->readFromFileHandle($csvFile->getId(), $handle, $delimeter) as $batch_data) {
                $this->dm->flush();
            }
        }

        return -1;
    }


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
     * @param $csvFileId
     * @param $handle
     * @param string $delimiter
     * @return array|\Generator
     */
    protected function readFromFileHandle(
        $csvFileId,
        $handle,
        string $delimiter
    )
    {
        $data = [];
        $i = 0;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

            $newFileData = new CsvFileData();
            $newFileData->setCsvFileId($csvFileId);
            $newFileData->setData($row);
            $this->dm->persist($newFileData);
            $i++;
            if ($i > 100) {
                $i = 0;
                yield $data;
            }
        }
        return $data;
    }
}