<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SendNewsletterCommand extends Command
{
    protected static $defaultName = 'app:send-newsletter';

    private $userRepository;
    private $mailer;

    public function __construct(UserRepository $userRepository, MailerInterface $mailer)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this->setDescription('Envoie une newsletter à tous les utilisateurs (Mailhog)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Envoi de la newsletter...");

        $users = $this->userRepository->findAll();
        $count = 0;

        foreach ($users as $user) {

            $email = (new Email())
                ->from('newsletter@monsite.com')
                ->to($user->getEmail())
                ->subject('Newsletter du jour')
                ->html("
                    <h1>Bonjour {$user->getPrenom()}</h1>
                    <p>Voici votre newsletter du jour 😄</p>
                ");

            $this->mailer->send($email);

            $io->text("📨 Newsletter envoyée à : " . $user->getEmail());
            $count++;
        }

        $io->success("Newsletter envoyée à $count utilisateurs !");
        return Command::SUCCESS;
    }
}
