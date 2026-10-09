<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

enum ProductSort
{
    case PriceAsc;
    case PriceDesc;
    case NameAsc;
    case NameDesc;
}
