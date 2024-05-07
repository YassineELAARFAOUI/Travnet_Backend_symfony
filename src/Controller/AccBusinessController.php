<?php

namespace App\Controller;

use App\Entity\AccBusiness;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccBusinessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/accBusiness', name: 'accBusiness', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['firstName'], $data['lastName'], $data['email'], $data['phone'], $data['cinOrPassport'], $data['country'], $data['password'],$data['pattenteDehotele'])) {
            return $this->json(['stateData' => 0], 200);
        }
        $emailExistsInAccBusiness = $this->entityManager->getRepository(AccBusiness::class)->findOneBy(['email' => $data['email']]);
        $cinOrPassportExistsInAccBusiness = $this->entityManager->getRepository(AccBusiness::class)->findOneBy(['cinOrPassport' => $data['cinOrPassport']]);
        $pattenteDehoteleExistsInAccBusiness = $this->entityManager->getRepository(AccBusiness::class)->findOneBy(['pattenteDehotele' => $data['pattenteDehotele']]);
        if($emailExistsInAccBusiness||$cinOrPassportExistsInAccBusiness ||$pattenteDehoteleExistsInAccBusiness){
            return $this->json([
                'existUser' => 1
            ]);
        }else{
            $user = new AccBusiness();
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setEmail($data['email']);
            $user->setPhone($data['phone']);
            $user->setCinOrPassport($data['cinOrPassport']);
            $user->setCountry($data['country']);
            $user->setPassword($data['password']);
            $user->setPattenteDehotele($data['pattenteDehotele']);
    

            try {
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $this->json([
                    'existUser' => 0,
                    'stateData' => 1,
                    'stateStore' => 1,
                    'userId' => $user->getId()
                ]);
            } catch (\Exception $e) {
                // Return JSON response with error message
                return $this->json([
                    'existUser' => 0,
                    'stateData' => 1,
                    'stateStore' => 0
                ]);
            }
        }


    }

    // login pour acc business
    #[Route('/loginAccBusiness', name: 'loginAccBusiness', methods: ['POST'])]
    public function loginAccBusiness(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['email'], $data['password'])) {
            return $this->json(['stateData' => 0], 200);
        }

        // Find the client by email
        $accBusiness = $entityManager->getRepository(AccBusiness::class)->findOneBy(['email' => $data['email']]);
        
        if (!$accBusiness) {
            return $this->json(['existUser' => 0]);
        } else {
            // Retrieve the block status of the client
            $isBlocked = $accBusiness->getBlock();
            
            if ($isBlocked) {
                return $this->json([
                    'existUser' => 1,
                    'isBlocked' => 1
                ]);
            } else {
                $passwordClientFromDatabase = $accBusiness->getPassword();
                
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

