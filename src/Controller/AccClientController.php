<?php

namespace App\Controller;
use App\Entity\AccClient;
use App\Entity\Reservation;
use App\Entity\Hotel;
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
            return $this->json(['state'=>0,'stateData' => 0], 200);
        }

        // Find the client by email
        $client = $entityManager->getRepository(AccClient::class)->findOneBy(['email' => $data['email']]);
        
        if (!$client) {
            return $this->json([
                'state'=>0,
                'stateData' => 1,
                'existUser' => 0,
            ]);
        } else {
            // Retrieve the block status of the client
            $isBlocked = $client->getBlock();
            
            if ($isBlocked) {
                return $this->json([
                    'state'=>1,
                    'stateData' => 1,
                    'existUser' => 1,
                    'isBlocked' => 1
                ]);
            } else {
                $passwordClientFromDatabase = $client->getPassword();
                
                    return $this->json([
                        'existUser' => 1,
                        'isBlocked' => 0,
                        //les attribues recuperees de client
                        'idClient'=>$client->getId(),
                        'email'=>$client->getEmail(),
                        'password' =>$passwordClientFromDatabase, 
                    ]);
                
            }
        }
    }

    //visualiser les reclamation
    #[Route('/recupererdetailReservation', name: 'recupererdetailReservation', methods: ['POST'])]
    public function recupererdetailReservation(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['clientId'], $data['currentDate'])) {
            return $this->json(['stateData' => 0], 200);
        }

        // Verify existence of client
        $iSClientExsit = $entityManager->getRepository(AccClient::class)->findOneBy([
            'id' => $data['clientId'],
        ]);
        if (!$iSClientExsit) {
            return $this->json(['state' => 0, 'stateData' => 1, 'iSClientExsit' => 0], 200);
        }

        // Convert string dates to DateTime objects
        $currentDate = new \DateTime($data['currentDate']);

        try {
            $clientId = $data['clientId'];

            // Requête pour récupérer toutes les réservations selon les conditions
            $qb = $entityManager->createQueryBuilder();
            $qb->select('r')
                ->from(Reservation::class, 'r')
                ->where('r.clientId = :clientId')
                ->andWhere(':currentDate BETWEEN r.datecheckin AND r.datecheckout')
                ->setParameter('clientId', $clientId)
                ->setParameter('currentDate', $currentDate);

            $ensembleDeReservations = $qb->getQuery()->getResult();
            if (!$ensembleDeReservations) {
                return $this->json([
                    'state' => 0,
                    'stateData' => 1,
                    'iSClientExsit' => 1,
                    'IsEnsembleReservationExsist' => 0,
                ], 200);
            }

            $reservationsDetails = [];
            foreach ($ensembleDeReservations as $reservation) {
                // Récupération des attributs
                $pateneteDehotel = $reservation->getPattenteDeHotel();
                $HotelObject = $entityManager->getRepository(Hotel::class)->findOneBy(['id' =>  $pateneteDehotel]);
                $nameOfHotel = $HotelObject->getName();
                $cityOfHotel = $HotelObject->getCity();
                $numeroDeChambre = $reservation->getNumeroDeChambre();
                $reservationsDetails[] = [
                    'pateneteDehotel' => $pateneteDehotel,
                    'nameOfHotel' => $nameOfHotel,
                    'cityOfHotel' => $cityOfHotel,
                    'numeroDeChambre' => $numeroDeChambre,
                ];
            }

            return $this->json([
                'state' => 1,
                'stateData' => 1,
                'iSClientExsit' => 1,
                'IsEnsembleReservationExsist' => 1,
                'reservations' => $reservationsDetails,
            ], 200);
        } catch (\Exception $e) {
            return $this->json(['state' => 0, 'error' => $e->getMessage()], 200);
        }
    }

    // route pour voir le catalogue des hôtels
    #[Route('/viewCatlogHotel', name: 'viewCatlogHotel', methods: ['POST'])]
    public function viewCatlogHotel(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['city']) || !isset($data['datecheckin']) || !isset($data['datecheckout']) || !isset($data['page'])) {
            return $this->json(['stateData' => 0], 200);
        }

        $city = $data['city'];
        $page = $data['page'];
        $pageSize = 9;
        $offset = ($page - 1) * $pageSize;

        // Création de la requête principale pour récupérer les prix min et max des chambres par hôtel
        $qb = $entityManager->createQueryBuilder();
        $qb->select('MIN(c.price) AS min, MAX(c.price) AS max, c.pattenteDeHotel AS hotel_id')
            ->addSelect('(SELECT h1.city FROM App\Entity\Hotel h1 WHERE h1.id = c.pattenteDeHotel) AS city')
            ->addSelect('(SELECT h3.img FROM App\Entity\Hotel h3 WHERE h3.id = c.pattenteDeHotel) AS img')
            ->addSelect('(SELECT h2.description FROM App\Entity\Hotel h2 WHERE h2.id = c.pattenteDeHotel) AS description')
            ->from('App\Entity\Chambres', 'c')
            ->where($qb->expr()->in('c.pattenteDeHotel', 
                $entityManager->createQueryBuilder()
                    ->select('sub_h.id')
                    ->from('App\Entity\Hotel', 'sub_h')
                    ->where('sub_h.city = :city')
                    ->getDQL()
            ))
            ->groupBy('c.pattenteDeHotel')
            ->setFirstResult($offset)
            ->setMaxResults($pageSize)
            ->setParameter('city', $city);

        $hotels = $qb->getQuery()->getResult();

        if (empty($hotels)) {
            return $this->json(['hotelExistent' => 0, 'stateData' => 1], 200);
        }

        $hotelsData = [];
        foreach ($hotels as $hotel) {
            $hotelsData[] = [
                'hotel_id' => $hotel['hotel_id'],
                'minPrice' => $hotel['min'],
                'maxPrice' => $hotel['max'],
                'city' => $hotel['city'],
                'img' => $hotel['img'],
                'description' => $hotel['description'],
                // Ajoutez ici d'autres données de l'hôtel que vous souhaitez inclure
            ];
        }

        return $this->json([
            'stateData' => 1,
            'hotelExistent' => 1,
            'hotels' => $hotelsData
        ]);
    }





    ////////////////////  route for 8001 front

    //blocker un client  cette route fait par Admin
    #[Route('/blockOrDeblockClient', name: 'blockOrDeblockClient', methods: ['POST'])]
    public function blockOrDeblockClient(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['id'], $data['block'])) {
            return $this->json(['state' => 0, 'stateData' => 0], 200);
        }

        // Use EntityManager to retrieve the client by id
        $client = $entityManager->getRepository('App\Entity\AccClient')->find($data['id']);
        if (empty($client)) {
            return $this->json(['clientExistent' => 0, 'state' => 1], 200);
        }

        // Toggle the block status
        $block = !$data['block'];
        $client->setBlock($block);

        try {
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->json([
                'state' => 1,
                'clientExistent' => 1
            ]);
        } catch (\Exception $e) {
            // Return JSON response with error message
            return $this->json([
                'clientExistent' => 1,
                'state' => 0
            ]);
        }
    }

    //route pour recuperer l'ensemble de client qui va envoyer au admin
    #[Route('/viewAllclient', name: 'viewAllclient', methods: ['GET'])]
    public function viewAllclient(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Utilisation de l'EntityManager pour récupérer tous les clients
        $allClients = $entityManager->getRepository('App\Entity\AccClient')->findAll();
        if (empty($allClients)) {
            return $this->json(['clientExistent' => 0, 'state' => 1], 200);
        }

        $clientsData = [];
        foreach ($allClients as $client) {
            $clientsData[] = [
                'ClientId' => $client->getId(),
                'fullName' => $client->getFullName(),
                'email' => $client->getEmail(),
                'phone' => $client->getPhone(),
                'password' => $client->getPassword(),
                'block' => $client->getBlock(),
                // Ajoutez ici d'autres données du client que vous souhaitez inclure
            ];
        }

        return $this->json([
            'state' => 1,
            'clientExistent' => 1,
            'allClients' => $clientsData
        ]);
    }

}