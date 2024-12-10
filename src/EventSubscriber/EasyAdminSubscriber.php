<?php
# src/EventSubscriber/EasyAdminSubscriber.php

namespace App\EventSubscriber;

use App\Controller\Admin\OrderCrudController;
use App\Entity\Order;
use App\Entity\User;
use App\Service\EmailService;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    // Constructor injects dependencies: password hasher and request stack
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private RequestStack $requestStack,
        private EmaiLService $emailService,
    ) {
    }

    /**
     * Registers the event listeners for EasyAdmin events.
     * - `BeforeEntityPersistedEvent`: Triggered before an entity is persisted to the database.
     * - `BeforeEntityDeletedEvent`: Triggered before an entity is deleted from the database.
     * - `BeforeEntityUpdatedEvent`: Triggered before an entity is updated in the database.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['BeforeEntityPersistedEvent'],
            BeforeEntityDeletedEvent::class => ['BeforeEntityDeletedEvent'],
            BeforeEntityUpdatedEvent::class => ['BeforeEntityUpdatedEvent'],
        ];
    }

    /**
     * Handles logic before an entity is updated.
     * - Updates user passwords if a new password is provided.
     * - Updates product stock for orders.
     */
    #[NoReturn]
    public function BeforeEntityUpdatedEvent(BeforeEntityUpdatedEvent $event): void
    {
        // Retrieve session for flash messages
        $session = $this->requestStack->getSession();

        // Get the entity being updated
        $entity = $event->getEntityInstance();

        // If the entity is a User, update the password if a new password is provided
        if ($entity instanceof User) {
            if ($entity->getNewPassword() !== '') {
                // Hash the new password
                $encodedPassword = $this->hasher->hashPassword($entity, $entity->getNewPassword());
                // Set the hashed password on the User entity
                $entity->setPassword($encodedPassword);
            }
        }

        // If the entity is an Order, handle stock updates for each OrderProduct
        if ($entity instanceof Order) {
            if($entity->getStatus() == 'ready' && !$entity->isReadyEmailSend())
            {
                $this->emailService->SendOrderIsReady(
                    $entity->getEmail(),
                    $entity->getNumber(),
                    $entity->getName()
                );

                $entity->setReadyEmailSend(true);
            }

            $this->UpdateStock($entity);
        }
    }

    /**
     * Handles logic before an entity is persisted.
     * - Updates product stock for orders.
     */
    #[NoReturn]
    public function BeforeEntityPersistedEvent(BeforeEntityPersistedEvent $event): void
    {
        // Get the entity being persisted
        $entity = $event->getEntityInstance();

        // If the entity is an Order, handle stock updates for each OrderProduct
        if ($entity instanceof Order) {
            $entity->setNumber(rand(10000000, 99999999)); // Generate random order number
            if($entity->getStatus() == 'ready' && !$entity->isReadyEmailSend())
            {
                $this->emailService->SendOrderIsReady(
                    $entity->getEmail(),
                    $entity->getNumber(),
                    $entity->getName()
                );

                $entity->setReadyEmailSend(true);
            }

            $this->UpdateStock($entity);
        }
    }

    /**
     * Handles logic before an entity is deleted.
     * - Placeholder for additional logic (currently empty).
     */
    #[NoReturn]
    public function BeforeEntityDeletedEvent(BeforeEntityDeletedEvent $event)
    {
        // Get the entity being deleted
        $entity = $event->getEntityInstance();

        if($entity instanceof User)
        {
            if($entity->getId() == 2)
                if (in_array('ROLE_ADMIN', $entity->getRoles()))
                {
                    throw new \Exception('Kan geen hoofd-administrator verwijderen!');
                }
        }

        // Logic for handling entity deletion can be added here
    }

    public function UpdateStock($entity)
    {
        foreach ($entity->getOrderProducts() as $orderProduct) {
            $session = $this->requestStack->getSession();

            if (!$orderProduct->isStockUpdated()) {
                $product = $orderProduct->getProduct();

                // Check if there is enough stock for the product
                if ($product->getStock() >= $orderProduct->getAmount()) {
                    // Deduct stock and mark the OrderProduct as stock updated
                    $product->setStock($product->getStock() - $orderProduct->getAmount());
                    $orderProduct->setStockUpdated(true);
                } else {
                    // If not enough stock, add a warning flash message and remove the OrderProduct
                    $session->getFlashBag()->add('warning', 'Product niet op voorraad!');
                    $entity->removeOrderProduct($orderProduct);
                }
            }
        }
    }
}