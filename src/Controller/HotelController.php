<?php

namespace App\Controller;
use App\Entity\AccBusiness;
use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HotelController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/addhotel', name: 'addhotel', methods: ['POST'])]
    public function addHotel(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['id'], $data['name'], $data['rate'], $data['location'], $data['city'],$data['description'],$data['img'],$data['idAccBussiness'])) {
            return $this->json(['stateData' => 0], 200);
        }
        $idExsistInACCBussiness = $this->entityManager->getRepository(AccBusiness::class)->findOneBy(['id' => $data['idAccBussiness']]);
        if(!($idExsistInACCBussiness)){
            return $this->json([
                'idAccBussiness' => 0
            ]); 
        } else{
                // Create new AccClient entity and set its properties
            $hotel = new Hotel();
            $hotel->setId($data['id']);
            $hotel->setName($data['name']);
            $hotel->setRate($data['rate']);
            $hotel->setLocation($data['location']);
            $hotel->setCity($data['city']);
            $hotel->setDescription($data['description']);
            $hotel->setImg($data['img']);
            $hotel->setIdAccBussiness($data['idAccBussiness']);

            try {
                $this->entityManager->persist($hotel);
                $this->entityManager->flush();
                return $this->json([
                    'idAccBussiness' => 1,
                    'state' => 1,
                    'hotelId' => $hotel->getId()
                    
                ]);
            } catch (\Exception $e) {
                // Return JSON response with error message
                return $this->json([
                    'idAccBussiness' => 1,
                    'state' => 0
                ]);
            }
        }

    }

     
}
