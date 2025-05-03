<?php declare(strict_types=1);

namespace Myfav\CraftImport\Dto;

class OptionDto {
    public function __construct(
        private string $propertyGroupId,
        private string $propertyGroupOptionId)
    {
    }

    public function getPropertyGroupId(): string
    {
        return $this->propertyGroupId;
    }

    public function getPropertyGroupOptionId(): string
    {
        return $this->propertyGroupOptionId;
    }
}