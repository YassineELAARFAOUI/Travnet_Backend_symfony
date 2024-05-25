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

}