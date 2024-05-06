<?php

namespace App\Controller;
use App\Entity\Test;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/addtest', name: 'addtest', methods: ['POST'])]
    public function addchambre(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['name'], $data['age'])) {
            return $this->json(['stateData' => 0], 200);
        }
        else{
                // Create new AccClient entity and set its properties
            $test = new Test();
            $test->setName($data['name']);
            $test->setAge($data['age']);
           

            try {
                $this->entityManager->persist($test);
                $this->entityManager->flush();
                return $this->json([
                    'idTest' => 1,
                    'state' => 1,
                    'chambreId' => $test->getId()
                    
                ]);
            } catch (\Exception $e) {
                // Return JSON response with error message
                return $this->json([
                    'idTest' => 1,
                    'state' => 0
                ]);
            }
        }

    }

     
}
