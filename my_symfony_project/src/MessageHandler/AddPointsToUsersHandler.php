<?php

namespace App\MessageHandler;

use App\Message\AddPointsToUsers;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

#[AsMessageHandler]
class AddPointsToUsersHandler
{
    public function __construct(private UserRepository $userRepo, private EntityManagerInterface $em) {}

    public function __invoke(AddPointsToUsers $message)
    {
        $users = $this->userRepo->findBy(['actif' => true]);

        foreach ($users as $user) {
            $user->setPoints($user->getPoints() + 1000);
        }

        $this->em->flush();
    }
}
