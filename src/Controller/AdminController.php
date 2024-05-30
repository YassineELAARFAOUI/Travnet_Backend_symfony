<?php

namespace App\Controller;
use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\Cloner\Data;

class AdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/addAdmin', name: 'addAdmin', methods: ['POST'])]
    public function addAdmin(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['name'], $data['email'], $data['password'])) {
            return $this->json(['stateData' => 0], 200);
        } else{
                // Create new AccClient entity and set its properties
            $user = new Admin();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            $user->setPassword($data['password']);

            try {
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $this->json([
                    'state' => 1,
                    'userId' => $user->getId()
                    
                ]);
            } catch (\Exception $e) {
                // Return JSON response with error message
                return $this->json([
                    
                'existUser' => 0,
                'state' => 0,
                'error' => $e->getMessage() // Include the error message for debugging
                    
                ]);
            }
        }

    }
   //login admin 
#[Route('/loginAdminfinal', name: 'loginAdminfinal', methods: ['POST'])]
public function loginAdminfina(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Valider les données JSON entrantes
    if (!$data || !isset($data['email'], $data['password'])) {
        return $this->json(['state' => 0, 'stateData' => 0], 200);
    }

    // Trouver le client par email
    $admin = $entityManager->getRepository(Admin::class)->findOneBy(['email' => $data['email']]);

    if (!$admin) {
        return $this->json([
            'state' => 0,
            'stateData' => 1,
            'existAdmin' => 0,
        ]);
    } else {
        $passwordAdminFromDataBase = $admin->getPassword();

        return $this->json([
            'existAdmin' => 1,
            'idClient' => $admin->getId(),
            'email' => $admin->getEmail(),
            'password' => $passwordAdminFromDataBase, 
        ]);
    }
}

}