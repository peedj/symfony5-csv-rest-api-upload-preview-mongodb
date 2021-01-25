<?php

namespace App\Repository;

use App\Document\CsvFile;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Environment;

/**
 * @method CsvFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method CsvFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method CsvFile[]    findAll()
 * @method CsvFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CsvFileRepository extends ServiceDocumentRepository implements ServiceSubscriberInterface
{
    public ContainerInterface $container;

    public function __construct(ManagerRegistry $registry, ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct($registry, CsvFile::class);
    }

    public function saveCsvFile(UploadedFile $file)
    {
        $filename = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
        $file->move(
            $this->getUploadsDir(),
            $filename
        );

        return $this->getUploadsDir() . "/" . $filename;
    }

    private function getUploadsDir()
    {
        return $this->getParameter('env(UPLOAD_FOLDER)');
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getParameter(string $name)
    {
        if (!$this->container->has('parameter_bag')) {
            throw new ServiceNotFoundException('parameter_bag.', null, null, [], sprintf('The "%s::getParameter()" method is missing a parameter bag to work properly. Did you forget to register your controller as a service subscriber? This can be fixed either by using autoconfiguration or by manually wiring a "parameter_bag" in the service locator passed to the controller.', static::class));
        }

        return $this->container->get('parameter_bag')->get($name);
    }


    public static function getSubscribedServices()
    {
        return [
            'parameter_bag' => '?' . ContainerBagInterface::class,
        ];
    }

}
