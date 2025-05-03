<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\ImportedArticle;

use Myfav\CraftImport\Core\Content\MyfavCraftImportArticle\MyfavCraftImportArticleEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ImportedArticleEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $myfavCraftImportArticleId;
    protected ?string $productId;
    protected ?string $parentProductId;
    
    protected ?MyfavCraftImportArticleEntity $myfavCraftImportArticle = null;
    protected ?ProductEntity $product = null;
    protected ?ProductEntity $parentProduct = null;

    // $myfavCraftImportArticleId
    public function getMyfavCraftImportArticleId(): ?string
    {
        return $this->myfavCraftImportArticleId;
    }

    public function setMyfavCraftImportArticleId(?string $myfavCraftImportArticleId): void
    {
        $this->myfavCraftImportArticleId = $myfavCraftImportArticleId;
    }

    // $productId
    public function getProductId(): ?string
    {
        return $this->productId;
    }

    public function setProductId(?string $productId): void
    {
        $this->productId = $productId;
    }

    // $parentProductId
    public function getParentProductId(): ?string
    {
        return $this->parentProductId;
    }

    public function setParentProductId(?string $parentProductId): void
    {
        $this->parentProductId = $parentProductId;
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

    // $product
    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    // $parentProduct
    public function getParentProduct(): ?ProductEntity
    {
        return $this->parentProduct;
    }

    public function setParentProduct(?ProductEntity $parentProduct): void
    {
        $this->parentProduct = $parentProduct;
    }
}