<?php
namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Psr\Log\LoggerInterface;

class SendEmailMessageHandler implements MessageHandlerInterface
{
    private $mailer;
    private $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function __invoke(SendEmailMessage $message)
    {
        try {
            $emailMessage = (new Email())
                ->from('sender-mail')
                ->to($message->getEmail())
                ->subject('Welcome to the platform!')
                ->text("Hello {$message->getName()}, your data has been successfully stored!");

            $this->mailer->send($emailMessage);
            $this->logger->info('Email sent successfully to ' . $message->getEmail());
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email: ' . $e->getMessage());
        }
    }
}
