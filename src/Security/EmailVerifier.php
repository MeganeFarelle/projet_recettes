<?php

namespace App\Security;

use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer
    ) {}

    public function sendEmailConfirmation(string $routeName, User $user, Email $email): void
    {
        $signature = $this->verifyEmailHelper->generateSignature(
            $routeName,
            $user->getId(),
            $user->getEmail()
        );

        $email->html("
            <h1>Hi! Please confirm your email!</h1>
            <p>Please confirm your email address by clicking the following link:</p>
            <a href='{$signature->getSignedUrl()}'>Confirm my Email</a>
            <p>This link will expire in 1 hour.</p>
            <br>Cheers!
        ");

        $this->mailer->send($email);
    }

    public function handleEmailConfirmation(User $user, string $signedUrl): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation(
            $signedUrl,
            $user->getId(),
            $user->getEmail()
        );
    }
}
