<?php

namespace App\MessageHandler;

use App\Entity\Notification;
use App\Message\SendMessage;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendMessageHandler
{
    private $userRepository;
    private $em;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    public function __invoke(SendMessage $message) 
    {
        $user = $this->userRepository->find($message->getUserId());
        if (!$user) {
            return;
        }

        $notification = new Notification();
        $notification->setUser($user);
        $notification->setLabel($message->getLabel());

        $this->em->persist($notification);
        $this->em->flush();
    }
}
