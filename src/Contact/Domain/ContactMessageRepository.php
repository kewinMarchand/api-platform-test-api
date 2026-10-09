<?php

declare(strict_types=1);

namespace App\Contact\Domain;

interface ContactMessageRepository
{
    public function save(ContactMessage $contactMessage): void;
}
