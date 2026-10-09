<?php

declare(strict_types=1);

namespace App\Contact\Infrastructure\Doctrine;

use App\Contact\Domain\ContactMessage;
use App\Contact\Domain\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineContactMessageRepository implements ContactMessageRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(ContactMessage $contactMessage): void
    {
        $this->entityManager->persist($contactMessage);
        $this->entityManager->flush();
    }
}
