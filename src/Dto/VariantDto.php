<?php declare(strict_types=1);

namespace Myfav\CraftImport\Dto;

class VariantDto {
    public function __construct(
        private array $shopwareFieldsForVariant,
        private ?array $optionIds)
    {
    }

    // shopwareFieldsForVariant
    public function getShopwareFieldsForVariant(): array
    {
        return $this->shopwareFieldsForVariant;
    }

    public function setVariantField(string $fieldName, mixed $value) {
        $this->shopwareFieldsForVariant[$fieldName] = $value;
    }

    // optionIds
    public function getOptionIds(): ?array
    {
        return $this->optionIds;
    }
}