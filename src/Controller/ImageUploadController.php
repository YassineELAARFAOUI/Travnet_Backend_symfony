<?php
// src/Controller/ImageUploadController.php
namespace App\Controller;
use App\Entity\Hotel;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImageUploadController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/upload', name: 'upload', methods: ['POST'])]
    public function upload(Request $request, ValidatorInterface $validator): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('image');
        $patent = $request->request->get('pattenteDehotele'); 
        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        // Validate the file
        $errors = $validator->validate($file, new Image([
            'maxSize' => '5M',
            'mimeTypes' => [
                'image/jpeg',
                'image/png',
                'image/gif',
            ],
        ]));

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['state' => 0 ,'errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Move the file to the upload directory
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
        $newFilename = uniqid() . '.' . $file->guessExtension();

        try {
            $isHotelExsist = $this->entityManager->getRepository(Hotel::class)->findOneBy(['id' => $patent]);
            $isHotelExsist->setImg($newFilename);
            $this->entityManager->persist($isHotelExsist);
            $this->entityManager->flush();
            try {
                $file->move($uploadDir, $newFilename);
                return $this->json([
                    'statename' => 1,
                    'stateimg' => 1,
                ]);
            } catch (\Exception $e) {
                return $this->json([
                    'statename' => 1,
                    'stateimg' => 0,
                ]);
            }
        }catch (\Throwable $e) {
            return $this->json([
                'statename' => 0,
                'stateimg' => 0
            ]);
        }
    }

    #[Route('/images', name: 'get_images', methods: ['GET'])]
    public function getImages(): JsonResponse
    {
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
        
        // Get all files in the directory
        $files = scandir($uploadDir);
        // Remove '.' and '..' from the list
        $files = array_diff($files, ['.', '..']);
        
        // Prepare list of filenames
        $imageList = [];
        foreach ($files as $file) {
            $imageList[] = $file;
        }

        return new JsonResponse($imageList);
    }
}
