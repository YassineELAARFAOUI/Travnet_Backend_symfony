<?php

namespace App\Controller;

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

class ReservationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/addreservation', name: 'addreservation', methods: ['POST'])]
    public function addReservation(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
    $data = json_decode($request->getContent(), true);

    // Validate incoming JSON data
    if (!$data || !isset($data['clientId'], $data['pattenteDeHotel'], $data['datecheckin'], $data['datecheckout'], $data['confirmation'], $data['numeroDeChambre'])) {
        return $this->json(['stateData' => 0], 200);
    }

    // Verify existence of client and hotel
    $iSHotelExsit = $entityManager->getRepository(Hotel::class)->findOneBy(['id' => $data['pattenteDeHotel']]);
    $iSClientExsit = $entityManager->getRepository(AccClient::class)->findOneBy(['id' => $data['clientId']]);
    $iSChambreExsit = $entityManager->getRepository(Chambres::class)->findOneBy([
        'pattenteDeHotel' => $data['pattenteDeHotel'],
        'numeroChambre' => $data['numeroDeChambre']
    ]);
    if (!$iSHotelExsit) {
        return $this->json(['stateData' => 1, 'iSHotelExsit' => 0], 200);
    }
    

    if (!$iSClientExsit) {
        return $this->json(['stateData' => 1, 'iSHotelExsit' => 1, 'iSClientExsit' => 0], 200);
    }
    if (!$iSChambreExsit) {
        return $this->json(['stateData' => 1, 'iSHotelExsit' => 1, 'iSClientExsit' => 1,'iSChambreExsit'=>0], 200);
    }

    // Convert string dates to DateTime objects
    $datecheckin = new \DateTime($data['datecheckin']);
    $datecheckout = new \DateTime($data['datecheckout']);

    if ($datecheckin > $datecheckout) {
        return $this->json(['stateData' => 1, 'iSHotelExsit' => 1, 'iSClientExsit' => 1,'iSChambreExsit'=>1 ,'dateValidation' => 0], 200);
    }

    try {
        $pattenteDeHotel = $data['pattenteDeHotel'];
        $numeroDeChambre = $data['numeroDeChambre'];

        // Requête pour vérifier la disponibilité de la chambre
        $qb = $entityManager->createQueryBuilder();
        $qb->select('r')
            ->from(Reservation::class, 'r')
            ->where('r.pattenteDeHotel = :pattenteDeHotel')
            ->andWhere('r.numeroDeChambre = :numeroDeChambre')
            ->andWhere('(:datecheckin BETWEEN r.datecheckin AND r.datecheckout OR :datecheckout BETWEEN r.datecheckin AND r.datecheckout OR r.datecheckin BETWEEN :datecheckin AND :datecheckout)')
            ->setParameter('pattenteDeHotel', $pattenteDeHotel)
            ->setParameter('numeroDeChambre', $numeroDeChambre)
            ->setParameter('datecheckin', $datecheckin)
            ->setParameter('datecheckout', $datecheckout);

        $existingReservations = $qb->getQuery()->getResult();

        if (count($existingReservations) > 0) {
            return $this->json(['state'=> 0,'stateData' => 1, 'iSHotelExsit' => 1, 'iSClientExsit' => 1,'iSChambreExsit'=>1,'dateValidation' => 1, 'availability' => 0], 200);
        }
        // Create new Reservation entity and set its properties
        $reservation = new Reservation();
        $reservation->setClientId($data['clientId']);
        $reservation->setPattenteDeHotel($data['pattenteDeHotel']);
        $reservation->setDatecheckin($datecheckin);
        $reservation->setDatecheckout($datecheckout);
        $reservation->setConfirmation($data['confirmation']);
        $reservation->setNumeroDeChambre($data['numeroDeChambre']);
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $this->json(['state' => 1,'reservationId' => $reservation->getId(),'stateData' => 1, 'iSHotelExsit' => 1, 'iSClientExsit' => 1, 'iSChambreExsit'=>1,'dateValidation' => 1, 'availability' => 1], 200);
    } catch (\Exception $e) {
        return $this->json(['state' => 0, 'error' => $e->getMessage()], 200);
    }
}

}