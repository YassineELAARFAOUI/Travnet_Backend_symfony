<?php

namespace App\Controller;

use App\Entity\Card;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PayementController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/addpayment', name: 'addpayment', methods: ['POST'])]
    public function addpayment(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        // Validate incoming JSON data
        if (!$data || 
            !isset($data['CardHolderName'], $data['cardNumber'], $data['dateExperation'], $data['cardVerification']) || 
            strlen($data['cardNumber']) !== 16 || 
            strlen($data['cardVerification']) !== 3
        ) {
            return $this->json(['stateData' => 0], 200);
        } else {
            // Create new card entity and set its propertie
    
                return $this->json([
                    'stateData' => 1,
                    'payement' => "payement done successfully"
                ]);
        }
    }
    
}