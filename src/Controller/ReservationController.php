<?php

namespace App\Controller;

use App\Entity\Reservation;
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
    public function addReservation(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['clientId'], $data['pattenteDeHotel'], $data['dateCheck_in'], $data['dateCheck_out'], $data['confirmation'])) {
            return $this->json(['stateData' => 0], 200);
        } else {
            // Create new Reservation entity and set its properties
            $reservation = new Reservation();
            $reservation->setClientId($data['clientId']);
            $reservation->setPattenteDeHotel($data['pattenteDeHotel']);

            // Convert string dates to DateTime objects
            $dateCheckIn = new DateTime($data['dateCheck_in']);
            $dateCheckOut = new DateTime($data['dateCheck_out']);

            $reservation->setDateCheck_in($dateCheckIn);
            $reservation->setDateCheck_out($dateCheckOut);
            $reservation->setConfirmation($data['confirmation']);

            try {
                $this->entityManager->persist($reservation);
                $this->entityManager->flush();
                return $this->json([
                    'state' => 1,
                    'reservationId' => $reservation->getId()
                ]);
            } catch (\Exception $e) {
                // Return JSON response with error message
                return $this->json([
                    'state' => 0
                ]);
            }
        }
    }
}
