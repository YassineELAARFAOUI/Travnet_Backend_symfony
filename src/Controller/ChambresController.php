<?php

namespace App\Controller;
use App\Entity\Chambres;
use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
class ChambresController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
//je fais une modification on doit transmer aussi entite auusssi 
    #[Route('/addchambre', name: 'addchambre', methods: ['POST'])]
    public function addchambre(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['numeroPersonne'], $data['numeroEtage'],$data['numeroChambre'], $data['price'], $data['surface'],$data['climatisation'], $data['salleDebain'],$data['dateDepublication'],$data['description'],$data['pattenteDeHotel'])) {
            return $this->json(['stateData' => 0], 200);
        }
        $idExsistInHotel = $this->entityManager->getRepository(Hotel::class)->findOneBy(['id' => $data['pattenteDeHotel']]);
        if(!($idExsistInHotel)){
            return $this->json([
                'pattenteDeHotel' => 0
            ]); 
        }
            // Check if the room number already exists in the specified hotel
        $existingRoom = $this->entityManager->getRepository(Chambres::class)->findOneBy([
            'pattenteDeHotel' => $data['pattenteDeHotel'],
            'numeroChambre' => $data['numeroChambre']
        ]);

        if ($existingRoom) {
            return $this->json(['roomExists' => 1], 200); // Room already exists
        }
        
        else{
                // Create new AccClient entity and set its properties
            $chambre = new Chambres();
            $chambre->setPattenteDeHotel($data['pattenteDeHotel']);
            $chambre->setNumeroPersonne($data['numeroPersonne']);
            $chambre->setNumeroEtage($data['numeroEtage']);
            $chambre->setNumeroChambre($data['numeroChambre']);
            $chambre->setPrice($data['price']);
            $chambre->setSurface($data['surface']);
            $chambre->setClimatisation($data['climatisation']);
            $chambre->setSalleDebain($data['salleDebain']);
            $chambre->setDateDepublication(new \DateTime($data['dateDepublication']));
            $chambre->setDescription($data['description']);
          
           
        
            
            try {
                $this->entityManager->persist($chambre);
                $this->entityManager->flush();
                return $this->json([
                    'pattenteDeHotel' => 1,
                    'roomExists' => 0,
                    'state' => 1,
                    'chambreId' => $chambre->getId()
                    
                ]);
            } catch (\Exception $e) {
                // Return JSON response with error message
                return $this->json([
                    'pattenteDeHotel' => 1,
                    'roomExists' => 0,
                    'state' => 0
                ]);
            }
        }

    }

//ajouter plusieurs chambres a la fois
// je fais une modification on doit transmer aussi entite auusssi 
#[Route('/addmultichambre', name: 'addmultichambre', methods: ['POST'])]
public function addmultichambre(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Validate incoming JSON data
    if (!$data || !isset($data['numeroPersonne'], $data['numeroEtage'], $data['numeroChambreDebut'], $data['numeroChambreFin'], $data['price'], $data['surface'], $data['climatisation'], $data['salleDebain'], $data['dateDepublication'], $data['description'], $data['pattenteDeHotel'])) {
        return $this->json(['stateData' => 0], 200);
    }

    // Check if the hotel exists
    $idExsistInHotel = $this->entityManager->getRepository(Hotel::class)->findOneBy(['id' => $data['pattenteDeHotel']]);
    if (!$idExsistInHotel) {
        return $this->json(['stateData' => 1,'pattenteDeHotel' => 0], 200);
    }

    $numeroChambreDebut = $data['numeroChambreDebut'];
    $numeroChambreFin = $data['numeroChambreFin'];

    // Ensure the room number range is valid
    if ($numeroChambreDebut > $numeroChambreFin) {
        return $this->json(['stateData' => 1,'pattenteDeHotel' => 1,'stateRange' => 0, ], 200);
    }

    // Check if any room number in the range already exists in the specified hotel
    $existingRooms = $this->entityManager->getRepository(Chambres::class)->createQueryBuilder('c')
        ->where('c.pattenteDeHotel = :hotelId')
        ->andWhere('c.numeroChambre BETWEEN :debut AND :fin')
        ->setParameter('hotelId', $data['pattenteDeHotel'])
        ->setParameter('debut', $numeroChambreDebut)
        ->setParameter('fin', $numeroChambreFin)
        ->getQuery()
        ->getResult();

    if (count($existingRooms) > 0) {
        return $this->json(['stateData' => 1,'pattenteDeHotel' => 1,'stateRange' => 1,'roomExists' => 1], 200);
    }

    try {
        for ($numeroChambre = $numeroChambreDebut; $numeroChambre <= $numeroChambreFin; $numeroChambre++) {
            // Create new Chambres entity and set its properties
            $chambre = new Chambres();
            $chambre->setPattenteDeHotel($data['pattenteDeHotel']);
            $chambre->setNumeroPersonne($data['numeroPersonne']);
            $chambre->setNumeroEtage($data['numeroEtage']);
            $chambre->setNumeroChambre($numeroChambre); // Set the current room number
            $chambre->setPrice($data['price']);
            $chambre->setSurface($data['surface']);
            $chambre->setClimatisation($data['climatisation']);
            $chambre->setSalleDebain($data['salleDebain']);
            $chambre->setDateDepublication(new \DateTime($data['dateDepublication']));
            $chambre->setDescription($data['description']);

            $this->entityManager->persist($chambre);
        }

        $this->entityManager->flush();

        return $this->json([
            'stateData' => 1,
            'pattenteDeHotel' => 1,
            'stateRange' => 1,
            'roomExists' => 0,
            'state' => 1
        ]);
    } catch (\Exception $e) {
        // Return JSON response with error message
        return $this->json([
            'stateData' => 1,
            'pattenteDeHotel' => 1,
            'roomExists' => 1,
            'state' => 0,
            'message' => $e->getMessage()
        ], 500);
    }
}



    //recupere les chmbres resrver et non reservee       
    #[Route('/recuperChambres', name: 'recuperChambres', methods: ['POST'])]
    public function recuperChambres(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['idAccBussiness']) || !isset($data['pattenteDeHotel']) || !isset($data['dateFilter'])) {
            return $this->json(['stateData' => 0], 200);
        }

        $pattenteDeHotel = $data['pattenteDeHotel'];
        $page = $data['page'];
        $pageSize = 5;
        $offset = ($page - 1) * $pageSize;
        $dateFilter = new \DateTime($data['dateFilter']);  // Assurez-vous que 'dateFilter' est une date valide

        $chambresRepository = $entityManager->getRepository(Chambres::class);

        // Requête pour récupérer les chambres avec leur disponibilité
        $qb = $chambresRepository->createQueryBuilder('c');
        $qb->select('c', "CASE WHEN c.numeroChambre IN (SELECT r.numeroDeChambre FROM App\Entity\Reservation r WHERE r.pattenteDeHotel = :pattenteDeHotelSubquery AND :dateFilter BETWEEN r.datecheckin AND r.datecheckout) THEN 1 ELSE 0 END AS available")
            ->andWhere('c.pattenteDeHotel = :pattenteDeHotel')
            ->setParameter('pattenteDeHotel', $pattenteDeHotel)
            ->setParameter('pattenteDeHotelSubquery', $pattenteDeHotel)
            ->setParameter('dateFilter', $dateFilter)
            ->setFirstResult($offset)
            ->setMaxResults($pageSize);

        $results = $qb->getQuery()->getResult();

        if (empty($results)) {
            return $this->json(['chambresExistent' => 0, 'stateData' => 1], 200);
        }

        $chambresData = [];
        foreach ($results as $result) {
            $chambre = $result[0]; // L'entité Chambre
            $available = $result['available']; // Le champ calculé

            $chambresData[] = [
                'numero_chambre' => $chambre->getNumeroChambre(),
                'numeroEtage' => $chambre->getNumeroEtage(),
                'available' => $available
            ];
        }

        // Requête pour compter le nombre total de chambres
        $qbCount = $chambresRepository->createQueryBuilder('c');
        $qbCount->select('COUNT(c.id)')
                ->andWhere('c.pattenteDeHotel = :pattenteDeHotel')
                ->setParameter('pattenteDeHotel', $pattenteDeHotel);

        $totalChambres = $qbCount->getQuery()->getSingleScalarResult();

        return $this->json([
            'stateData' => 1,
            'chambresExistent' => 1,
            'totalChambres' => $totalChambres,
            'chambres' => $chambresData
        ]);
    }


    // Route pour récupérer les numéros de chambre selon la disponibilité et les dates de check-in et de check-out
    #[Route('/recupererNumeroEnsembleChambre', name: 'recupererNumeroEnsembleChambre', methods: ['POST'])]
    public function recupererNumeroEnsembleChambre(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérification des données requises
        if (!$data || !isset($data['datecheckin']) || !isset($data['datecheckout']) || !isset($data['pattenteDeHotel'])) {
            return $this->json(['stateData' => 0], 200);
        }

        // Extraction des données de la requête
        $pattenteDeHotel = $data['pattenteDeHotel'];
        $datecheckin = new \DateTime($data['datecheckin']);
        $datecheckout = new \DateTime($data['datecheckout']);

        // Vérification de la validité des dates de check-in et de check-out
        if ($datecheckout < $datecheckin) {
            return $this->json(['stateData' => 1, 'validationCheckinCheckout' => 0], 200);
        }

        $chambresRepository = $entityManager->getRepository(Chambres::class);

        // Requête pour récupérer les chambres avec leur disponibilité
        $qb = $chambresRepository->createQueryBuilder('c');
        $qb->select('c.numeroChambre')
            ->andWhere('c.pattenteDeHotel = :pattenteDeHotel')
            ->andWhere($qb->expr()->notIn(
                'c.numeroChambre',
                $entityManager->createQueryBuilder()
                    ->select('r.numeroDeChambre')
                    ->from('App\Entity\Reservation', 'r')
                    ->where('r.pattenteDeHotel = :pattenteDeHotelSubquery')
                    ->andWhere('(r.datecheckout >= :datecheckin AND r.datecheckin <= :datecheckout)')
                    ->getDQL()
            ))
            ->setParameter('pattenteDeHotel', $pattenteDeHotel)
            ->setParameter('pattenteDeHotelSubquery', $pattenteDeHotel)
            ->setParameter('datecheckin', $datecheckin)
            ->setParameter('datecheckout', $datecheckout);

        $results = $qb->getQuery()->getResult();

        if (empty($results)) {
            return $this->json(['chambresExistent' => 0, 'stateData' => 1], 200);
        }

        $chambresData = array_map(function($result) {
            return ['numero_chambre' => $result['numeroChambre']];
        }, $results);

        return $this->json([
            'stateData' => 1,
            'chambresExistent' => 1,
            'validationCheckinCheckout' => 1,
            'chambres' => $chambresData
        ]);
    }


}