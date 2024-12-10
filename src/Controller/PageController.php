<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\TreatmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    #[Route(path: '/', name: 'app_home')]
    public function index(ProductRepository $productRepository, TreatmentRepository $treatmentRepository): Response
    {
        // Render homepage with treatments and top 5 products
        return $this->render('page/home.html.twig', [
            'treatments' => $treatmentRepository->FindAll(),
            'topProducts' => $productRepository->findTop5MostSoldProducts(),
        ]);
    }
}
