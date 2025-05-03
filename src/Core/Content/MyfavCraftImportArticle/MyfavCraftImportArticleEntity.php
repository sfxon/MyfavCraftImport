<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\MyfavCraftImportArticle;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class MyfavCraftImportArticleEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $craftProductNumber;
    
    // @var array<string, mixed>|null
    protected $craftData;

    // @var array<string, mixed>|null
    protected $customData;

    // $craftProductNumber
    public function getCraftProductNumber(): ?string
    {
        return $this->craftProductNumber;
    }

    public function setCraftProductNumber(?string $craftProductNumber): void
    {
        $this->craftProductNumber = $craftProductNumber;
    }

    // craftData
    /**
     * @return array<string, mixed>|null
     */
    public function getCraftData(): ?array
    {
        return $this->craftData;
    }

    /**
     * @param array<string, mixed> $craftData
     */
    public function setCraftData(array $craftData): void
    {
        $this->craftData = $craftData;
    }

    // customData
    /**
     * @return array<string, mixed>|null
     */
    public function getCustomData(): ?array
    {
        return $this->customData;
    }

    /**
     * @param array<string, mixed> $customData
     */
    public function setCustomData(array $customData): void
    {
        $this->customData = $customData;
    }
}