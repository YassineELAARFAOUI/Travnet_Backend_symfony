<?php

namespace App\Controller;

use App\Entity\Reclamation;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReclamationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/addreclamation', name: 'addreclamation', methods: ['POST'])]
    public function addReclamation(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['email'], $data['description'], $data['pattenteDeHotel'], $data['numeroChambre'], $data['dateDereclamation'])) {
            return $this->json(['stateData' => 0], 200);
        } 
        else {
            // Create new Reclamation entity and set its properties
            $reclamation = new Reclamation();
            $reclamation->setEmail($data['email']);
            $reclamation->setDescription($data['description']);
            $reclamation->setPattenteDeHotel($data['pattenteDeHotel']);
            $reclamation->setNumeroChambre($data['numeroChambre']);
            // Convert string dates to DateTime objects
            $dateDereclamation = new DateTime($data['dateDereclamation']);
            $reclamation->setDateDereclamation($dateDereclamation);
          
            try {
                $this->entityManager->persist($reclamation);
                $this->entityManager->flush();
                return $this->json([
                    'state' => 1,
                    'reclamationId' => $reclamation->getId()
                ]);
            } catch (\Exception $e) {
                // Return JSON response with error message
                return $this->json([
                    'state' => 0
                ]);
            }
        }
    }
}
