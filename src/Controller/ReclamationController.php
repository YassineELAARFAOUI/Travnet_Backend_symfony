<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reservation;
use App\Entity\Hotel;
use App\Entity\AccClient;
use App\Entity\Chambres;
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
    // ajouter ici une reclamation
    #[Route('/addreclamation', name: 'addreclamation', methods: ['POST'])]
    public function addReclamation(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['clientId'],$data['email'], $data['description'], $data['pattenteDeHotel'], $data['numeroChambre'])) {
            return $this->json(['stateData' => 0], 200);
        } 
            // Check if the hotel exists
        $idExsistInHotel = $this->entityManager->getRepository(Hotel::class)->findOneBy(['id' => $data['pattenteDeHotel']]);
            // Verfication de email exists en cient entity
        $emailExsistInAccClient = $this->entityManager->getRepository(AccClient::class)->findOneBy([
            'email' => $data['email'],
            'id' => $data['clientId'],
        ]);
        if (!$emailExsistInAccClient) {
            return $this->json(['stateData' => 1,'emailExsistInAccClient' => 0], 200);
        }    
        if (!$idExsistInHotel) {
            return $this->json(['stateData' => 1,'emailExsistInAccClient' => 1,'pattenteDeHotel' => 0], 200);
        }
        else {

            $clientId = $data['clientId'];
            $pattenteDeHotel = $data['pattenteDeHotel'];
            $numeroDeChambre = $data['numeroChambre'];
            $currentDate = new \DateTime();

            // Requête pour vérifier la disponibilité de la chambre
            $qb = $entityManager->createQueryBuilder();
            $qb->select('r')
                ->from(Reservation::class, 'r')
                ->where('r.pattenteDeHotel = :pattenteDeHotel')
                ->andWhere('r.numeroDeChambre = :numeroDeChambre')
                ->andWhere('(:currentDate BETWEEN r.datecheckin AND r.datecheckout)')
                ->andWhere('r.clientId = :clientId')
                ->setParameter('pattenteDeHotel', $pattenteDeHotel)
                ->setParameter('numeroDeChambre', $numeroDeChambre)
                ->setParameter('currentDate', $currentDate)
                ->setParameter('clientId', $clientId);

            $logementEnCours = $qb->getQuery()->getResult();
            if(!$logementEnCours){
                return $this->json(['state'=> 0,'stateData' => 1,'emailExsistInAccClient' => 1,'pattenteDeHotel' => 1,"logementEnCours"=>0], 200);
            }
            // Create new Reclamation entity and set its properties
            $reclamation = new Reclamation();
            $reclamation->setEmail($data['email']);
            $reclamation->setDescription($data['description']);
            $reclamation->setPattenteDeHotel($data['pattenteDeHotel']);
            $reclamation->setNumeroChambre($data['numeroChambre']);
            $reclamation->setDateDereclamation($currentDate);
            
            try {
                $this->entityManager->persist($reclamation);
                $this->entityManager->flush();
                return $this->json([
                    'stateData' => 1,
                    'emailExsistInAccClient' => 1,
                    'pattenteDeHotel' => 1,
                    'state' => 1,
                    'reclamationId' => $reclamation->getId(),
                    "logementEnCours"=>1,
                ]);
            } catch (\Exception $e) {
                // Return JSON response with error message
                return $this->json([
                    'state' => 0
                ]);
            }
        }
  }
    // recuperer l'ensemble des reclamations 
    #[Route('/recupererreclamation', name: 'recupererreclamation', methods: ['POST'])]
    public function recupererReclamation(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['pattenteDeHotel'])) {
            return $this->json(['stateData' => 0], 200);
        } 

        // Check if the hotel exists
        $hotel = $entityManager->getRepository(Hotel::class)->find($data['pattenteDeHotel']);    
        if (!$hotel) {
            return $this->json(['stateData' => 1,'pattenteDeHotel' => 0,'state' => 0], 200);
        }

        // Fetch reclamations for the given hotel
        $qb = $entityManager->createQueryBuilder();
        $qb->select('r')
            ->from(Reclamation::class, 'r')
            ->where('r.pattenteDeHotel = :pattenteDeHotel')
            ->setParameter('pattenteDeHotel', $data['pattenteDeHotel']);
        $reclamations = $qb->getQuery()->getResult();

        if (empty($reclamations)) {
            return $this->json(['state' => 0, 'stateData' => 1, 'pattenteDeHotel' => 1, "reclamations" => 0], 200);
        }

        // Process each reclamation to get client ID and room number
        $reclamationsData = [];
        foreach ($reclamations as $reclamation) {
            $chambreUnitaire = $entityManager->getRepository(Chambres::class)->findOneBy([
                'numeroChambre' => $reclamation->getNumeroChambre(),
                'pattenteDeHotel' => $data['pattenteDeHotel'],
            ]);
            $reclamateur = $entityManager->getRepository(AccClient::class)->findOneBy([
                'email' => $reclamation->getEmail()
            ]);
            
            $fullName=$reclamateur->getFullName();

            $reclamationsData[] = [
                'reclamationId' => $reclamation->getId(),
                'nomClient' => $fullName,
                'numero_chambre' => $reclamation->getNumeroChambre(),
                'numeroEtage' => $chambreUnitaire->getNumeroEtage(),
            ];
        }

        return $this->json([
            'stateData' => 1,
            'emailExsistInAccClient' =>1,
            'pattenteDeHotel' => 1,
            'state' => 1,
            'reclamations' => $reclamationsData,
        ], 200);
    }
    //recuperer juste une reclamation unitaire
    #[Route('/recupererreclamationunitaire', name: 'recupererreclamationunitaire', methods: ['POST'])]
    public function recupererReclamationUnitaire(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['pattenteDeHotel'], $data['idReclamation'])) {
            return $this->json(['stateData' => 0], 200);
        }

        // Check if the hotel exists
        $hotel = $entityManager->getRepository(Hotel::class)->find($data['pattenteDeHotel']);  
        $reclamationIsExsist = $entityManager->getRepository(Reclamation::class)->find($data['idReclamation']);  
        if (!$hotel) {
            return $this->json(['stateData' => 1, 'pattenteDeHotel' => 0], 200);
        }
        if (!$reclamationIsExsist) {
            return $this->json([
                'stateData' => 1,
                'pattenteDeHotel' => 1,
                'reclamationIsExsist' => 0,
            ], 200);
        }

        // Fetch reclamations for the given hotel
        $qb = $entityManager->createQueryBuilder();
        $qb->select('r')
            ->from(Reclamation::class, 'r')
            ->where('r.id = :idReclamation')
            ->setParameter('idReclamation', $data['idReclamation']);
        $reclamations = $qb->getQuery()->getResult();

        if (empty($reclamations)) {
            return $this->json([
                'state' => 0, 
                'stateData' => 1,
                'pattenteDeHotel' => 1,
                'reclamationIsExsist' => 1,
                "isExsistReclamation" => 0,
            ], 200);
        }

        // Since getResult returns an array, we should take the first result
        $reclamation = $reclamations[0];

        // Process reclamation to get information
        $chambreUnitaire = $entityManager->getRepository(Chambres::class)->findOneBy([
            'numeroChambre' => $reclamation->getNumeroChambre(),
            'pattenteDeHotel' => $data['pattenteDeHotel'],
        ]);
        $reclamateur = $entityManager->getRepository(AccClient::class)->findOneBy([
            'email' => $reclamation->getEmail()
        ]);

        if (!$chambreUnitaire || !$reclamateur) {
            return $this->json(['stateData' => 0], 200);
        }

        $fullName = $reclamateur->getFullName();
        $phone = $reclamateur->getPhone();
        $reclamationUnitaireData = [
            'nomClient' => $fullName,
            'numeroEtage' => $chambreUnitaire->getNumeroEtage(),
            'numero_chambre' => $chambreUnitaire->getNumeroChambre(),
            'phone' => $phone,
            'email' => $reclamation->getEmail(),
            'descriptionReclamation' => $reclamation->getDescription(),
        ];

        return $this->json([
            'state' => 1, 
            'stateData' => 1,
            'pattenteDeHotel' => 1,
            'reclamationIsExsist' => 1,
            "isExsistReclamation" => 1,
            'reclamations' => $reclamationUnitaireData,
        ], 200);
    }
}