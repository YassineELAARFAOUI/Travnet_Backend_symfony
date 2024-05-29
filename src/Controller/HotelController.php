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
            $hotel->setImg('defaultprofileimag.jpeg');
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

    //route pour recuperer l'image de hotel selon ca pattente
    #[Route('/recupererimagehotel', name: 'recupererimagehotel', methods: ['POST'])]
    public function recupererimagehotel(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['pattenteDehotele'])) {
            return $this->json(['stateData' => 0], 200);
        }
        $isHotelExsist = $this->entityManager->getRepository(Hotel::class)->findOneBy(['id' => $data['pattenteDehotele']]);
        if(!($isHotelExsist)){
            return $this->json([
                'pattenteDehotele' => 0
            ]); 
        } else{

            try {
                // Recuperer l'image de Hotel
                $pictureOfHotel=$isHotelExsist->getImg();
               
                return $this->json([
                    'isHotelExsist' => 1,
                    'state' => 1,
                    'hotelId' => $isHotelExsist->getId(),
                    'pictureOfHotel' => $pictureOfHotel,
                    
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

    //route pour recuperer un hotel selon son patente 
    #[Route('/recupererHotelParpattenete', name: 'recupererHotelParpattenete', methods: ['POST'])]
    public function recupererHotelParpattenete(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['pattenteDehotele'])) {
            return $this->json(['stateData' => 0], 200);
        }
        $isHotelExsist = $this->entityManager->getRepository(Hotel::class)->findOneBy(['id' => $data['pattenteDehotele']]);
        if(!($isHotelExsist)){
            return $this->json([
                'pattenteDehotele' => 0
            ]); 
        } else{

            try {
            
                return $this->json([
                    'isHotelExsist' => 1,
                    'state' => 1,
                    'hotelName' => $isHotelExsist->getName(),
                    'descriptionOfHotel' => $isHotelExsist->getDescription(),
                    'cityOfHotel' => $isHotelExsist->getCity(),
                    'locationOfHotel' => $isHotelExsist->getLocation(),
                    'rateofHotel' => $isHotelExsist->getRate(),
                    'imageName' => $isHotelExsist->getImg(),
                    
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
