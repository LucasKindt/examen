<?php
namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendOrderConfirmationEmail(string $to, string $orderNumber, string $customerName): void
    {
        // Make email
        $email = (new TemplatedEmail())
            ->to($to)
            ->subject('Orderbevestiging #' . $orderNumber)
            ->htmlTemplate('emails/order_confirmation.html.twig')
            ->context([
                'orderNumber' => $orderNumber,
                'customerName' => $customerName,
            ]);
        // Send email
        $this->mailer->send($email);
    }

    public function SendOrderIsReady(string $to, string $orderNumber, string $customerName): void
    {
        // Make email
        $email = (new TemplatedEmail())
            ->to($to)
            ->subject('Uw bestelling is klaar om op te halen! #' . $orderNumber)
            ->htmlTemplate('emails/order_ready.html.twig')
            ->context([
                'orderNumber' => $orderNumber,
                'customerName' => $customerName,
            ]);
        // Send email
        $this->mailer->send($email);
    }
}
