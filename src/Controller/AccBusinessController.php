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
        if (!$data || !isset($data['email'])) {
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
                return $this->json([
                    'existUser' => 1,
                    'isBlocked' => 0,
                    'userId' => $accBusiness->getId(),
                    'userEmail' => $accBusiness->getEmail(),
                    'userPassword' => $accBusiness->getPassword(),
                    'pateneteDeHotel' => $accBusiness->getPattenteDehotele(),
                ]);
            }
        }
    }




    ////////////////////  route for 8001 front


    //blocker or deblocker un accbuissines  cette route fait par Admin
    #[Route('/blockOrDeblockaccBusiness', name: 'blockOrDeblockaccBusiness', methods: ['POST'])]
    public function blockOrDeblockaccBusiness(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['id'], $data['block'])) {
            return $this->json(['state' => 0, 'stateData' => 0], 200);
        }

        // Use EntityManager to retrieve the client by id
        $accBusiness = $entityManager->getRepository('App\Entity\AccBusiness')->find($data['id']);
        if (empty($accBusiness)) {
            return $this->json(['accBusinessExistent' => 0, 'state' => 1], 200);
        }

        // Toggle the block status
        $block = !$data['block'];
        $accBusiness->setBlock($block);

        try {
            $entityManager->persist($accBusiness);
            $entityManager->flush();

            return $this->json([
                'state' => 1,
                'accBusinessExistent' => 1
            ]);
        } catch (\Exception $e) {
            // Return JSON response with error message
            return $this->json([
                'accBusinessExistent' => 1,
                'state' => 0
            ]);
        }
    }

    //route pour recuperer l'ensemble de client qui va envoyer au admin
    #[Route('/viewAllaccBusiness', name: 'viewAllaccBusiness', methods: ['GET'])]
    public function viewAllaccBusiness(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Utilisation de l'EntityManager pour récupérer tous les clients
        $allAccBusiness = $entityManager->getRepository('App\Entity\AccBusiness')->findAll();
        if (empty($allAccBusiness)) {
            return $this->json(['AccBusinessExistent' => 0, 'state' => 1], 200);
        }

        $AccBussinessData = [];
        foreach ($allAccBusiness as $accBusiness) {
            $firstName = $accBusiness->getFirstName();
            $lastName =$accBusiness->getLastName();
            $fullName =$firstName . ' ' . $lastName;
            $AccBussinessData[] = [
                'AccBusinessId' => $accBusiness->getId(),
                'fullName' => $fullName,
                'email' => $accBusiness->getEmail(),
                'phone' => $accBusiness->getPhone(),
                'cinOrPassport' => $accBusiness->getCinOrPassport(),
                'country' => $accBusiness->getCountry(),
                'password' => $accBusiness->getPassword(),
                'pattenteDehotele' => $accBusiness->getPattenteDehotele(),
                'block' => $accBusiness->getBlock(),
                // Ajoutez ici d'autres données du client que vous souhaitez inclure
            ];
        }

        return $this->json([
            'state' => 1,
            'AccBusinessExistent' => 1,
            'allAccBusiness' => $AccBussinessData
        ]);
    }



    /////////////////// home statistique
    
    //avoir les statitiques d'un hotel bien prcise le nombre de chmabre et reservation par mois et reclmatation
    #[Route('/statisticHotel', name: 'statisticHotel', methods: ['POST'])]
    public function statisticHotel(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérification des données requises
        if (!$data || !isset($data['pattenteDeHotel']) || !isset($data['currentDate'])) {
            return $this->json(['stateData' => 0], 400);
        }

        // Extraction des données de la requête
        $patenteDeHotel = $data['pattenteDeHotel'];
        $currentDate = new \DateTime($data['currentDate']);
        $firstDayOfMonth = (clone $currentDate)->modify('first day of this month')->format('Y-m-d');
        $lastDayOfMonth = (clone $currentDate)->modify('last day of this month')->format('Y-m-d');

        // Requête SQL globale
        $sql = "
            SELECT 
                COUNT(c.id) AS nbchamber,
                (SELECT COUNT(r.id) FROM reclamation r WHERE r.pattente_de_hotel = :patenteDeHotel) AS nbrec,
                (SELECT COUNT(r2.id) FROM reservation r2 WHERE r2.pattente_de_hotel = :patenteDeHotel AND r2.datecheckin BETWEEN :firstDayOfMonth AND :lastDayOfMonth) AS nbReservation
            FROM chambres c 
            WHERE c.pattente_de_hotel = :patenteDeHotel;
        ";

        // Création et exécution de la requête native
        $query = $entityManager->createNativeQuery($sql, new \Doctrine\ORM\Query\ResultSetMappingBuilder($entityManager));
        $query->setParameter('patenteDeHotel', $patenteDeHotel);
        $query->setParameter('firstDayOfMonth', $firstDayOfMonth);
        $query->setParameter('lastDayOfMonth', $lastDayOfMonth);

        // Mapping des résultats
        $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($entityManager);
        $rsm->addScalarResult('nbchamber', 'nbchamber');
        $rsm->addScalarResult('nbrec', 'nbrec');
        $rsm->addScalarResult('nbReservation', 'nbReservation');
        
        $query->setResultSetMapping($rsm);
        $result = $query->getSingleResult();

        if (!$result) {
            return $this->json(['chambresExistent' => 0, 'stateData' => 1], 200);
        }

        return $this->json([
            'stateData' => 1,
            'chambresExistent' => 1,
            'chambresCount' => $result['nbchamber'],
            'reservationCount' => $result['nbReservation'],
            'reclamationCount' => $result['nbrec'],
        ]);
    }
    

    //avoir de diagrame verticales sur le nombre de reservation pour chaque jour (6 date)
    #[Route('/diagrameVertical', name: 'diagrameVertical', methods: ['POST'])]
    public function diagrameVertical(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérification des données requises
        if (!$data || !isset($data['pattenteDeHotel']) || !isset($data['date1']) || !isset($data['date2']) || !isset($data['date3']) || !isset($data['date4']) || !isset($data['date5']) || !isset($data['date6'])) {
            return $this->json(['stateData' => 0], 400);
        }

        // Extraction des données de la requête
        $pattenteDeHotel = $data['pattenteDeHotel'];
        $date1 = new \DateTime($data['date1']);
        $date2 = new \DateTime($data['date2']);
        $date3 = new \DateTime($data['date3']);
        $date4 = new \DateTime($data['date4']);
        $date5 = new \DateTime($data['date5']);
        $date6 = new \DateTime($data['date6']);

        // Construction de la commande SQL
        $sql = "SELECT 
                    COUNT(*) AS D1,
                    (SELECT COUNT(*) FROM reservation r2 WHERE r2.pattente_de_hotel = '$pattenteDeHotel' AND r2.datecheckin = '{$date1->format('Y-m-d')}') AS D2,
                    (SELECT COUNT(*) FROM reservation r3 WHERE r3.pattente_de_hotel = '$pattenteDeHotel' AND r3.datecheckin = '{$date2->format('Y-m-d')}') AS D3,
                    (SELECT COUNT(*) FROM reservation r4 WHERE r4.pattente_de_hotel = '$pattenteDeHotel' AND r4.datecheckin = '{$date3->format('Y-m-d')}') AS D4,
                    (SELECT COUNT(*) FROM reservation r5 WHERE r5.pattente_de_hotel = '$pattenteDeHotel' AND r5.datecheckin = '{$date4->format('Y-m-d')}') AS D5,
                    (SELECT COUNT(*) FROM reservation r6 WHERE r6.pattente_de_hotel = '$pattenteDeHotel' AND r6.datecheckin = '{$date5->format('Y-m-d')}') AS D6
                FROM 
                    reservation r
                WHERE 
                    r.pattente_de_hotel = '$pattenteDeHotel' 
                    AND r.datecheckin = '{$date6->format('Y-m-d')}'";

        // Exécution de la commande SQL
        $connection = $entityManager->getConnection();
        $results = $connection->executeQuery($sql)->fetchAssociative();

        // Vérification des résultats
        if (!$results) {
            return $this->json(['stateData' => 0], 400);
        }

        return $this->json([
            'stateData' => 1,
            'reservationExistent' => 1,
            'D1' => $results['D1'],
            'D2' => $results['D2'],
            'D3' => $results['D3'],
            'D4' => $results['D4'],
            'D5' => $results['D5'],
            'D6' => $results['D6']
        ]);
    }
}

