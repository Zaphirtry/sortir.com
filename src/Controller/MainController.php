<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Campus;
use App\Form\FiltresType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET','POST'])]
    public function home(SortieRepository $sortieRepository,Request $request, EntityManagerInterface $em): Response
    {
      // Créer le formulaire de recherche
      $searchForm = $this->createForm(FiltresType::class);
      $searchForm->handleRequest($request);

      // Récupérer le repository pour faire la requête
      $repository = $em->getRepository(Sortie::class);

      // Construire la requête de base
      $queryBuilder = $repository->createQueryBuilder('s');

      if ($searchForm->isSubmitted() && $searchForm->isValid()) {
        // Récupérer les données du formulaire
        $data = $searchForm->getData();

        if (!empty($data['nom'])) {
          $queryBuilder->andWhere('s.nom LIKE :nom')
            ->setParameter('nom', '%' . $data['nom'] . '%');
        }

        if (!empty($data['dateDebut'])) {
          $queryBuilder->andWhere('s.dateHeureDebut >= :dateDebut')
            ->setParameter('dateDebut', $data['dateDebut']);
        }

        if (!empty($data['dateFin'])) {
          $queryBuilder->andWhere('s.dateHeureDebut <= :dateFin')
            ->setParameter('dateFin', $data['dateFin']);
        }
        if (!empty($data['campus'])) {
          $queryBuilder->andWhere('s.campus = :campus')
            ->setParameter('campus', $data['campus']);
        }
        if(!empty($data['mesSortiesOrganisees'])){
          $queryBuilder->andWhere('s.organisateur = :organisateur')
            ->setParameter('organisateur', $this->getUser());
        }
        if(!empty($data['mesSortiesParticipe'])){
          $queryBuilder->orWhere(':user MEMBER OF s.participant')
            ->setParameter('user', $this->getUser());
        }
        if (!empty($data['sortiesPassees'])) {
          $queryBuilder->andWhere('s.etat = :etat')
            ->setParameter('etat', 17);
        }
        if (!empty($data['sortiesAnnulee'])) {
          $queryBuilder->andWhere('s.etat = :etat')
            ->setParameter('etat', 18);
        }
      }

      $sorties = $queryBuilder->getQuery()->getResult();

      return $this->render('main/home.html.twig', [
        'sorties' => $sorties,
        'searchForm' => $searchForm->createView(),
      ]);
//        return $this->render('main/home.html.twig',[
//           'sorties' => $sortieRepository->findAll()
//        ]);
    }

}
