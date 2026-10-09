<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

/**
 * Prix en centimes d'euro. Les fronts manipulent des euros : la conversion se fait chez eux,
 * l'API ne connaît que les centimes.
 */
final readonly class Price
{
    private function __construct(public int $cents)
    {
    }

    public static function fromCents(int $cents): self
    {
        if ($cents < 0) {
            throw new \InvalidArgumentException(\sprintf('Un prix ne peut pas être négatif : %d centimes.', $cents));
        }

        return new self($cents);
    }

    public function compareTo(self $other): int
    {
        return $this->cents <=> $other->cents;
    }

    /**
     * Bornes incluses, null = sans borne.
     */
    public function isBetween(?self $min, ?self $max): bool
    {
        return (null === $min || $this->cents >= $min->cents)
            && (null === $max || $this->cents <= $max->cents);
    }
}
