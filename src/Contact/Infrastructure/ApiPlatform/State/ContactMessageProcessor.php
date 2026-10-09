<?php

declare(strict_types=1);

namespace App\Contact\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Contact\Domain\ContactMessage;
use App\Contact\Domain\ContactMessageRepository;
use App\Contact\Infrastructure\ApiPlatform\Resource\ContactMessageResource;

/**
 * @implements ProcessorInterface<ContactMessageResource, ContactMessageResource>
 */
final readonly class ContactMessageProcessor implements ProcessorInterface
{
    public function __construct(private ContactMessageRepository $contactMessageRepository)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ContactMessageResource
    {
        $contactMessage = new ContactMessage(
            trim((string) $data->name),
            trim((string) $data->email),
            trim((string) $data->message),
            new \DateTimeImmutable(),
        );
        $this->contactMessageRepository->save($contactMessage);

        $data->id = $contactMessage->getId();
        $data->name = $contactMessage->getName();
        $data->email = $contactMessage->getEmail();
        $data->message = $contactMessage->getMessage();
        $data->receivedAt = $contactMessage->getReceivedAt();

        return $data;
    }
}
