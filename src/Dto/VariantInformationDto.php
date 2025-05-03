<?php declare(strict_types=1);

namespace Myfav\CraftImport\Dto;

class VariantInformationDto {
    public function __construct(
        private string $productId,
        private string $productNumber,
        private bool $active)
    {
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getProductNumber(): string
    {
        return $this->productNumber;
    }

    // active
    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}