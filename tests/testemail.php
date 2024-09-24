<?php
require '../vendor/autoload.php';


use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

// Replace with your SMTP configuration
$dsn = 'smtp://20bec106@iiitdmj.ac.in:heJVthKqnQYw0wGk@mail.smtp2go.com:2525';

$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

$email = (new Email())
    ->from('20bec106@iiitdmj.ac.in')
    ->to('jitureddy1@gmail.com') // Change to a valid recipient email
    ->subject('Test Email')
    ->text('This is a test email.');

try {
    $mailer->send($email);
    echo "Email sent successfully!";
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage();
}
