<?php


namespace App\Controller;

use App\Repository\CsvFileRepository;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;

class CsvFileController
{
//    private $csfFileRepository;
//
//    public function __construct(CsvFileRepository $csfFileRepository)
//    {
//        $this->csfFileRepository = $csfFileRepository;
//    }
//
//    /**
//     * @Rest\FileParam(name="image", description="The background of the list", nullable=false, image=true)
//     * @param Request $request
//     * @param ParamFetcher $paramFetcher
//     * @param TaskList $list
//     * @return \FOS\RestBundle\View\View
//     */
//    public function backgroundListsAction(Request $request, ParamFetcher $paramFetcher, TaskList $list)
//    {
//        $currentBackground = $list->getBackground();
//        if (!is_null($currentBackground)) {
//            $filesystem = new Filesystem();
//            $filesystem->remove(
//                $this->getUploadsDir() . $currentBackground
//            );
//        }
//        /** @var UploadedFile $file */
//        $file = ($paramFetcher->get('image'));
//
//        if ($file) {
//            $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
//            $file->move(
//                $this->getUploadsDir(),
//                $filename
//            );
//
//            $list->setBackground($filename);
//            $list->setBackgroundPath('/uploads/' . $filename);
//
//            $this->entityManager->persist($list);
//            $this->entityManager->flush();
//
//            $data = $request->getUriForPath(
//                $list->getBackgroundPath()
//            );
//
//            return $this->view($data, Response::HTTP_OK);
//        }
//
//        return $this->view(['message' => 'Something went wrong'], Response::HTTP_BAD_REQUEST);
//    }
//
//    /**
//     * @return Response
//     */
//    public function listAction()
//    {
//        $items = $this->csfFileRepository->findAll();
//        $view = $this->view($items, Response::HTTP_OK , []);
//        return $this->handleView($view);
//    }
}