<?php

namespace App\Controller;
use App\Entity\AccClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\Cloner\Data;

class AccClientController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/accClient', name: 'acc_client', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['fullName'], $data['email'], $data['phone'], $data['password'])) {
            return $this->json(['stateData' => 0], 200);
        }
        $emailExistsInAccClient = $this->entityManager->getRepository(AccClient::class)->findOneBy(['email' => $data['email']]);
         if($emailExistsInAccClient){
            return $this->json([
                'existUser' => 1
            ]); 
         }else{
                // Create new AccClient entity and set its properties
            $user = new AccClient();
            $user->setFullName($data['fullName']);
            $user->setEmail($data['email']);
            $user->setPhone($data['phone']);
            $user->setPassword($data['password']);
            $user->setBlock(0);

            try {
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $this->json([
                    'existUser' => 0,
                    'state' => 1,
                    'userId' => $user->getId()
                    
                ]);
            } catch (\Exception $e) {
                // Return JSON response with error message
                return $this->json([
                    'existUser' => 0,
                    'state' => 0
                ]);
            }
        }

    }
 
    #[Route('/loginClient', name: 'loginClient', methods: ['POST'])]
    public function loginClient(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['email'], $data['password'])) {
            return $this->json(['stateData' => 0], 200);
        }

        // Find the client by email
        $client = $entityManager->getRepository(AccClient::class)->findOneBy(['email' => $data['email']]);
        
        if (!$client) {
            return $this->json(['existUser' => 0]);
        } else {
            // Retrieve the block status of the client
            $isBlocked = $client->getBlock();
            
            if ($isBlocked) {
                return $this->json([
                    'existUser' => 1,
                    'isBlocked' => 1
                ]);
            } else {
                $passwordClientFromDatabase = $client->getPassword();
                
                if ($passwordClientFromDatabase === $data['password']) {
                    return $this->json([
                        'existUser' => 1,
                        'isBlocked' => 0,
                        'password' => 1
                    ]);
                } else {
                    return $this->json([
                        'existUser' => 1,
                        'isBlocked' => 0,
                        'password' => 0
                    ]); 
                }
            }
        }
    }

}