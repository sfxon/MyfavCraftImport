<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\MyfavVereinArticle;

use Myfav\CraftImport\Core\Content\MyfavCraftImportArticle\MyfavCraftImportArticleEntity;
use Myfav\CraftImport\Core\Content\MyfavVerein\MyfavVereinEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class MyfavVereinArticleEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $myfavCraftImportArticleId;
    protected ?string $myfavVereinId;
    protected $customProductSettings;
    protected $overriddenCustomProductSettings;
    protected $variations;
    protected ?MyfavCraftImportArticleEntity $myfavCraftImportArticle;
    protected ?MyfavVereinEntity $myfavVerein;

    // $myfavCraftImportArticleId
    public function getMyfavCraftImportArticleId(): ?string
    {
        return $this->myfavCraftImportArticleId;
    }

    public function setMyfavCraftImportArticleId(?string $myfavCraftImportArticleId): void
    {
        $this->myfavCraftImportArticleId = $myfavCraftImportArticleId;
    }

    // $myfavVereinId
    public function getMyfavVereinId(): ?string
    {
        return $this->myfavVereinId;
    }

    public function setMyfavVereinId(?string $myfavVereinId): void
    {
        $this->myfavVereinId = $myfavVereinId;
    }

    // customProductSettings
    /**
     * @return array<string, mixed>|null
     */
    public function getCustomProductSettings(): ?array
    {
        return $this->customProductSettings;
    }

    /**
     * @param array<string, mixed> $customProductSettings
     */
    public function setCustomProductSettings(array $customProductSettings): void
    {
        $this->customProductSettings = $customProductSettings;
    }

    // overriddenCustomProductSettings
    /**
     * @return array<string, mixed>|null
     */
    public function getOverriddenCustomProductSettings(): ?array
    {
        return $this->overriddenCustomProductSettings;
    }

    /**
     * @param array<string, mixed> $overriddenCustomProductSettings
     */
    public function setOverriddenCustomProductSettings(array $overriddenCustomProductSettings): void
    {
        $this->overriddenCustomProductSettings = $overriddenCustomProductSettings;
    }

    // variations
    /**
     * @return array<string, mixed>|null
     */
    public function getVariations(): ?array
    {
        return $this->variations;
    }

    /**
     * @param array<string, mixed> $variations
     */
    public function setVariations(array $variations): void
    {
        $this->variations = $variations;
    }

    // $myfavCraftImportArticle
    public function getMyfavCraftImportArticle(): ?MyfavCraftImportArticleEntity
    {
        return $this->myfavCraftImportArticle;
    }

    public function setMyfavCraftImportArticle(?MyfavCraftImportArticleEntity $myfavCraftImportArticle): void
    {
        $this->myfavCraftImportArticle = $myfavCraftImportArticle;
    }

    // $myfavVerein
    public function getMyfavVerein(): ?MyfavVereinEntity
    {
        return $this->myfavVerein;
    }

    public function setMyfavVerein(?MyfavVereinEntity $myfavVerein): void
    {
        $this->myfavVerein = $myfavVerein;
    }
}