<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Repository\ProductRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    /**
     * Displays the cart page.
     * - Retrieves the cart from the session.
     * - Loads product details and calculates the total price.
     */
    #[Route('/cart', name: 'cart_index')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        // Get cart data from session or default to an empty array
        $cart = $session->get('cart', []);
        $cartData = [];
        $total = 0;

        // Loop through cart items to fetch product details and calculate totals
        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                $cartData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];
                $total += $product->getPrice() * $quantity;
            }
        }

        // Render the cart view with product and total price data
        return $this->render('cart/index.html.twig', [
            'cart' => $cartData,
            'total' => $total,
        ]);
    }

    /**
     * Adds a product to the cart or updates its quantity.
     * - Handles AJAX and regular form submissions.
     */
    #[Route('/cart/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function add(
        int $id,
        Request $request,
        SessionInterface $session,
        ProductRepository $productRepository
    ): Response {
        // Get quantity from the request (default to 1)
        $quantity = (int) $request->request->get('quantity', 1);
        $redirect = $request->request->get('redirect', 'cart');
        $cart = $session->get('cart', []);

        // Fetch the product entity
        $product = $productRepository->find($id);
        if (!$product) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => $product->getName().' niet gevonden.'], 404);
            }
            $this->addFlash('danger', $product->getName().' niet gevonden.');
            return $this->redirectToRoute('app_home');
        }

        if($product->getStock() < $quantity)
        {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Onvoldoende voorraad voor ' . $product->getName().'.'], 404);
            }
            $this->addFlash('warning', 'Onvoldoende voorraad voor ' . $product->getName().'.');

            if ($redirect === 'cart') {
                return $this->redirectToRoute('cart_index');
            }

            return $this->redirectToRoute('app_webshop');
        }

        // Add product to cart or update its quantity
        if (!isset($cart[$id])) {
            $cart[$id] = $quantity;
        } else {
            $cart[$id] += $quantity;
        }

        $session->set('cart', $cart);

        // Return JSON response for AJAX requests
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => sprintf('%s is toegevoegd aan je winkelwagen!', $product->getName()),
                'updatedQuantity' => $cart[$id],
            ]);
        }

        $this->addFlash('success', sprintf('%s is toegevoegd aan je winkelwagen!', $product->getName()));

        // Redirect based on the button action
        if ($redirect === 'cart') {
            return $this->redirectToRoute('cart_index');
        }

        return $this->redirectToRoute('app_webshop_detail', ['id' => $id]);
    }

    /**
     * Removes a product from the cart.
     * - Handles both AJAX and regular requests.
     */
    #[Route('/cart/remove/{id}', name: 'cart_remove', methods: ['POST'])]
    public function remove(
        int $id,
        Request $request,
        SessionInterface $session,
        ProductRepository $productRepository
    ): Response {
        // Get the current cart from the session
        $cart = $session->get('cart', []);

        // Check if the product exists in the cart
        if (isset($cart[$id])) {
            // Get the quantity to remove from the request
            $quantityToRemove = (int) $request->request->get('quantity', 1);

            // Calculate the new quantity after removal
            $newQuantity = $cart[$id] - $quantityToRemove;

            // If the new quantity is less than 1, remove the product entirely
            if ($newQuantity < 1) {
                unset($cart[$id]);
            } else {
                // Otherwise, update the cart with the reduced quantity
                $cart[$id] = $newQuantity;
            }

            // Update the session cart
            $session->set('cart', $cart);
        }

        // Handle AJAX requests
        if ($request->isXmlHttpRequest()) {
            $updatedQuantity = $cart[$id] ?? 0; // Check updated quantity or default to 0 if removed
            return $this->json([
                'success' => true,
                'message' => $updatedQuantity > 0
                    ? 'Product is bijgewerkt in je winkelwagen.'
                    : 'Product is verwijderd uit je winkelwagen.',
                'updatedQuantity' => $updatedQuantity,
            ]);
        }

        // Add flash message for non-AJAX requests
        $this->addFlash('success', 'Product is bijgewerkt in je winkelwagen.');

        // Redirect to the cart page
        return $this->redirectToRoute('cart_index');
    }


    /**
     * Handles the checkout process.
     * - Creates an Order entity.
     * - Links products in the cart to the order.
     * - Sends an order confirmation email.
     */
    #[Route('/cart/checkout', name: 'cart_checkout', methods: ['POST'])]
    public function checkout(
        Request $request,
        SessionInterface $session,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response {
        // Retrieve cart data from the session
        $cart = $session->get('cart', []);
        if (empty($cart)) {
            $this->addFlash('warning', 'Uw winkelwagen is leeg.');
            return $this->redirectToRoute('cart_index');
        }

        // Create a new Order entity
        $order = new Order();
        $order->setName($request->request->get('name'));
        $order->setEmail($request->request->get('email'));
        $order->setPhone($request->request->get('phone'));
        $order->setNumber(rand(10000000, 99999999)); // Generate random order number
        $order->setStatus('pending');
        $order->setDate(new \DateTime());

        $entityManager->persist($order);

        // Create OrderProduct entities for each cart item
        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if (!$product) {
                continue;
            }

            if($product->getStock() < $quantity)
            {
                $this->addFlash('warning', 'Onvoldoende voorraad voor ' . $product->getName());
                return $this->redirectToRoute('cart_index');
            }

            $orderProduct = new OrderProduct();
            $orderProduct->setTOrder($order);
            $orderProduct->setProduct($product);
            $orderProduct->setAmount($quantity);

            $entityManager->persist($orderProduct);
        }

        $entityManager->flush();

        // Clear the cart
        $session->remove('cart');

        // Send order confirmation email
        $emailService->sendOrderConfirmationEmail(
            $order->getEmail(),
            $order->getNumber(),
            $order->getName()
        );

        return $this->redirectToRoute('cart_success', ['order' => $order->getId()]);
    }

    /**
     * Displays the success page after a successful checkout.
     */
    #[Route('/cart/success/{order}', name: 'cart_success')]
    public function success(Order $order): Response
    {
        return $this->render('cart/confirmation.html.twig', [
            'order' => $order,
        ]);
    }
}