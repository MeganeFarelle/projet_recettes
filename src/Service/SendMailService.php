<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use App\Entity\User;

class SendMailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(
        string $from,
        string $to,
        string $subject,
        string $template,
        array $context
    ): void
    {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("emails/$template.html.twig")
            ->context($context);

        $this->mailer->send($email);
    }

    /**
     * VERSION RÉELLE : dire bonjour à UN utilisateur
     */
    public function dire_bonjour(User $user): void
    {
        $this->send(
            'no-reply@monsite.net',               
            $user->getEmail(),                     
            'Bonjour',                              
            'bonjour',                              
            [
                // ⚠️ Correction obligatoire : NE PAS utiliser "email"
                'userEmail' => $user->getUserIdentifier()
            ]
        );
    }

    /**
     * Dire bonjour à TOUS les utilisateurs
     */
    public function dire_bonjour_a_tous(array $users): void
    {
        foreach ($users as $user) {
            $this->dire_bonjour($user);
        }
    }
}

