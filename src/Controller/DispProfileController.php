<?php

namespace App\Controller;

use App\Entity\AccBusiness;
use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DispProfileController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    //route pour recuperer les informations de account bussiness
    #[Route('/disprofile', name: 'disprofile', methods: ['POST'])]
    public function DispProfile(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['idAccBussiness'])) {
            return $this->json(['stateData' => 0], 200);
        }

        // Récupère l'objet complet de AccBusiness selon l'ID
        $AccBusinessObject = $this->entityManager->getRepository(AccBusiness::class)->find($data['idAccBussiness']);

        if (!$AccBusinessObject) {
            return $this->json(['stateDataAccBussiness' => 0], 200);
        }

        // Extraire chaque attribut dans une variable distincte
        $firstName = $AccBusinessObject->getFirstName();
        $lastName = $AccBusinessObject->getLastName();
        $fullName = $firstName . ' ' . $lastName;
        $email = $AccBusinessObject->getEmail();
        $phone = $AccBusinessObject->getPhone();
        $cinOrPassport = $AccBusinessObject->getCinOrPassport();
        $country = $AccBusinessObject->getCountry();
        $patenteDeHotel = $AccBusinessObject->getPattenteDehotele();

        // Extraction de données de l'hôtel lié à AccBusiness
        $HotelObject = $this->entityManager->getRepository(Hotel::class)->find($patenteDeHotel);

        if (!$HotelObject) {
            return $this->json(['stateDataHotel' => 0], 200);
        }

        // Extraire chaque attribut dans une variable distincte
        $nameHotel = $HotelObject->getName();
        $descriptionHotel = $HotelObject->getDescription();
        $locationHotel = $HotelObject->getLocation();
        $rateHotel = $HotelObject->getRate();
        $cityOfHotel = $HotelObject->getCity();

        // Retourner les attributs sous forme de réponse JSON
        return $this->json([
            'stateData' => 1,
            'stateDataAccBussiness' => 1,
            'stateDataHotel' => 1,
            'accBusiness' => [
                'fullName' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'cinOrPassport' => $cinOrPassport,
                'country' => $country,
                'patenteDeHotel'=>$patenteDeHotel,
            ],
            'hotel' => [
                'name' => $nameHotel,
                'description' => $descriptionHotel,
                'location' => $locationHotel,
                'rate' => $rateHotel,
                'city' => $cityOfHotel,
            ]
        ], 200);
    }
     //route pour modifier les informations de account bussiness
     #[Route('/modifierinfoprofile', name: 'modifierinfoprofile', methods: ['POST'])]
     public function modifierInfoProfile(Request $request): JsonResponse
     {
         $data = json_decode($request->getContent(), true);
 
         // Validate incoming JSON data
         if (!$data || !isset($data['idAccBussiness'],$data['firstName'],$data['lastName'],$data['phone'],$data['country'],$data['patenteDeHotel'])) {
             return $this->json(['stateDataAccBusiness' => 0,'actionModifier' => 0], 200);
         }
         if (!$data || !isset($data['name'],$data['description'],$data['location'],$data['rate'],$data['city'])) {
            return $this->json(['stateDataHotel' => 0,'actionModifier' => 0], 200);
        }
 
         // Récupère l'objet complet de AccBusiness selon l'ID
         $AccBusinessObject = $this->entityManager->getRepository(AccBusiness::class)->find($data['idAccBussiness']);
 
         if (!$AccBusinessObject) {
             return $this->json(['stateDataExistanceAccBussiness' => 0,'actionModifier' => 0], 200);
         }
 
         // Extraire chaque attribut dans une variable distincte
         $AccBusinessObject->setFirstName($data['firstName']);
         $AccBusinessObject->setLastName($data['lastName']);
         $AccBusinessObject->setPhone($data['phone']);
         $AccBusinessObject->setCountry($data['country']);
         // Informer l'EntityManager qu'il y a des modifications à persister
        $this->entityManager->persist($AccBusinessObject);
        // Effectuer la mise à jour dans la base de données
        $this->entityManager->flush();

         // Extraction de données de l'hôtel lié à AccBusiness
         $HotelObject = $this->entityManager->getRepository(Hotel::class)->find($data['patenteDeHotel']);
         if (!$HotelObject) {
             return $this->json(['stateDataExistanceHotel' => 0,'actionModifier' => 0], 200);
         }
 
         // Extraire chaque attribut dans une variable distincte
         $HotelObject->setName($data['name']);
         $HotelObject->setDescription($data['description']);
         $HotelObject->setLocation($data['location']);
         $HotelObject->setRate($data['rate']);
         $HotelObject->setCity($data['city']);
         // Informer l'EntityManager qu'il y a des modifications à persister
        $this->entityManager->persist($HotelObject);
        // Effectuer la mise à jour dans la base de données
        $this->entityManager->flush();
 
         // Retourner les attributs sous forme de réponse JSON
         return $this->json([
             'stateData' => 1,
             'stateDataAccBussiness' => 1,
             'stateDataHotel' => 1,
             'actionModifier' => 1
         ], 200);
     }
}