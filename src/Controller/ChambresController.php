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

    #[Route('/addchambre', name: 'addchambre', methods: ['POST'])]
    public function addchambre(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate incoming JSON data
        if (!$data || !isset($data['numeroPersonne'], $data['numeroChambre'], $data['price'], $data['surface'],$data['climatisation'], $data['salleDebain'],$data['dateDepublication'],$data['description'],$data['pattenteDeHotel'])) {
            return $this->json(['stateData' => 0], 200);
        }
        $idExsistInHotel = $this->entityManager->getRepository(Hotel::class)->findOneBy(['id' => $data['pattenteDeHotel']]);
        if(!($idExsistInHotel)){
            return $this->json([
                'pattenteDeHotel' => 0
            ]); 
        } else{
                // Create new AccClient entity and set its properties
            $chambre = new Chambres();
            $chambre->setPattenteDeHotel($data['pattenteDeHotel']);
            $chambre->setNumeroPersonne($data['numeroPersonne']);
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
                    'state' => 1,
                    'chambreId' => $chambre->getId()
                    
                ]);
            } catch (\Exception $e) {
                // Return JSON response with error message
                return $this->json([
                    'pattenteDeHotel' => 1,
                    'state' => 0
                ]);
            }
        }

    }

     
}
