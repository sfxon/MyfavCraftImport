<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\ImportedVereinArticle;

use Myfav\CraftImport\Core\Content\MyfavVereinArticle\MyfavVereinArticleEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ImportedVereinArticleEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $myfavVereinArticleId;
    protected ?string $productId;
    protected ?string $parentProductId;
    
    protected ?MyfavVereinArticleEntity $myfavVereinArticle = null;
    protected ?ProductEntity $product = null;
    protected ?ProductEntity $parentProduct = null;

    // $myfavVereinArticleId
    public function getMyfavVereinArticleId(): ?string
    {
        return $this->myfavVereinArticleId;
    }

    public function setMyfavVereinArticleId(?string $myfavVereinArticleId): void
    {
        $this->myfavVereinArticleId = $myfavVereinArticleId;
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

    // $myfavVereinArticle
    public function myfavVereinArticle(): ?MyfavVereinArticleEntity
    {
        return $this->myfavVereinArticle;
    }

    public function setMyfavCraftImportArticle(?MyfavVereinArticleEntity $myfavVereinArticle): void
    {
        $this->myfavVereinArticle = $myfavVereinArticle;
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