<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\MyfavVerein;

use Myfav\CraftImport\Core\Content\MyfavCraftImportArticle\MyfavCraftImportArticleEntity;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class MyfavVereinEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;
    protected ?string $productNumberToken;
    protected ?string $categoryId;
    
    protected ?CategoryEntity $category = null;

    // $name
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    // $productNumberToken
    public function getProductNumberToken(): ?string
    {
        return $this->productNumberToken;
    }

    public function setProductNumberToken(?string $productNumberToken): void
    {
        $this->productNumberToken = $productNumberToken;
    }

    // $categoryId
    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function setCategoryId(?string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    // $category
    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }
}