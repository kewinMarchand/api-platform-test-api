<?php

declare(strict_types=1);

namespace App\Contact\Domain;

class ContactMessage
{
    private ?int $id = null;

    public function __construct(
        private string $name,
        private string $email,
        private string $message,
        private \DateTimeImmutable $receivedAt,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getReceivedAt(): \DateTimeImmutable
    {
        return $this->receivedAt;
    }
}
