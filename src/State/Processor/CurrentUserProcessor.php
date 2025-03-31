<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use LogicException;

final class CurrentUserProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object
    {
        $user = $this->security->getUser();

        if (!$user instanceof UserInterface) {
            throw new LogicException('No user logged in.');
        }

        if (method_exists($data, 'setUser')) {
            $data->setUser($user);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
