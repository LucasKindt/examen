<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebshopController extends AbstractController
{
    #[Route('/webshop', name: 'app_webshop')]
    public function index(ProductRepository $productRepository): Response
    {
        // Render webshop index with products array
        return $this->render('webshop/index.html.twig', [
            'products' => $productRepository->FindAll()
        ]);
    }

    #[Route('/webshop/{id}', name: 'app_webshop_detail')]
    public function detail(Product $product): Response
    {
        // Render product detail with product entity
        return $this->render('webshop/detail.html.twig', [
            'product' => $product
        ]);
    }
}
