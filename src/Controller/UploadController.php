<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\SendEmailMessage;
use Psr\Log\LoggerInterface; // Add the logger interface

class UploadController
{
    private $entityManager;
    private $bus;
    private $logger; // Declare a logger property

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $bus, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus; // Using Messenger to dispatch messages for asynchronous email sending
        $this->logger = $logger; // Inject the logger
    }

    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
    
        // Check if a file was uploaded
        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded.'], 400);
        }
    
        // Check if the file is valid and readable
        if (!$file->isValid()) {
            return new JsonResponse(['error' => 'Uploaded file is not valid.'], 400);
        }
    
        // Open the file for reading
        if (($handle = fopen($file->getPathname(), "r")) !== false) {
            fgetcsv($handle); // Skip header
    
            $users = []; // Array to hold users for persistence
    
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $user = new User();
                $user->setName($data[0]);
                $user->setEmail($data[1]);
                $user->setUsername($data[2]);
                $user->setAddress($data[3]);
                $user->setRole($data[4]);
    
                $users[] = $user; // Collect user for batch processing
            }
    
            // Persist all users and flush them to the database
            foreach ($users as $user) {
                $this->entityManager->persist($user);
                $this->bus->dispatch(new SendEmailMessage($user->getEmail(), $user->getName())); // Dispatch for each user
            }
    
            $this->entityManager->flush(); // Save all users to the database
            fclose($handle); // Ensure the file is closed
        }
    
        return new JsonResponse(['status' => 'File uploaded successfully']);
    }
}
