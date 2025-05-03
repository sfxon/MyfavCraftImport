<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Myfav\CraftImport\Core\Content\ImportedArticle\ImportedArticleEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class ImportedArticleService
{
    public function __construct(
        private readonly EntityRepository $importedArticleRepository,
        )
    {
    }

    /**
     * create
     *
     * @param  Context $context
     * @param  string $myfavCraftImportArticleId
     * @param  string $productId
     * @param  string|null $parentProductId
     * @return string
     */
    public function create(
        Context $context,
        string $myfavCraftImportArticleId,
        string $productId,
        ?string $parentProductId): string
    {
        $id = Uuid::randomHex();

        $data = [
            'id' => $id,
            'myfavCraftImportArticleId' => $myfavCraftImportArticleId,
            'productId' => $productId,
            'parentProductId' => $parentProductId
        ];
        $this->importedArticleRepository->upsert([$data], $context);

        return $id;
    }

    /**
     * getEntryByProductNumber
     *
     * @param  Context $context
     * @param  string $productNumber
     * @return null|ImportedArticle
     */
    public function getEntryByProductNumber(Context $context, string $productNumber): ?ImportedArticleEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addFilter(new EqualsFilter('product.productNumber', $productNumber));
        return $this->importedArticleRepository->search($criteria, $context)->first();
    }

    /**
     * getEntryByIdValues
     *
     * @param  Context $context
     * @param  string $myfavCraftImportArticleId
     * @param  string $productId
     * @param  string|null $parentProductId
     * @return ImportedArticleEntity|null
     */
    public function getEntryByIdValues(
        Context $context,
        string $myfavCraftImportArticleId,
        string $productId,
        ?string $parentProductId): ?ImportedArticleEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addFilter(new EqualsFilter('myfavCraftImportArticleId', $myfavCraftImportArticleId));
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->addFilter(new EqualsFilter('parentProductId', $parentProductId));
        return $this->importedArticleRepository->search($criteria, $context)->first();
    }

    /**
     * getVariantEntriesByParentId
     *
     * @param  Context $context
     * @param  string $myfavCraftImportArticleId
     * @param  string|null $parentProductId
     * @return ImportedArticleEntity|null
     */
    public function getVariantEntriesByParentId(
        Context $context,
        string $myfavCraftImportArticleId,
        string $parentProductId): mixed
    {
        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addFilter(new EqualsFilter('myfavCraftImportArticleId', $myfavCraftImportArticleId));
        $criteria->addFilter(new EqualsFilter('parentProductId', $parentProductId));
        return $this->importedArticleRepository->search($criteria, $context);
    }

    /**
     * upsert
     *
     * @param  Context $context
     * @param  string $myfavCraftImportArticleId
     * @param  string $productId
     * @param  string|null $parentProductId
     * @return string
     */
    public function upsert(
        Context $context,
        string $myfavCraftImportArticleId,
        string $productId,
        ?string $parentProductId): string
    {
        $id = null;
        $importedProduct = $this->getEntryByIdValues($context, $myfavCraftImportArticleId, $productId, $parentProductId);

        if($importedProduct !== null) {
            $id = $importedProduct->getId();
        } else {
            $id = $this->create($context, $myfavCraftImportArticleId, $productId, $parentProductId);
        }

        return $id;
    }

    /**
     * upsertVariants
     *
     * @param  Context $context
     * @param  string $myfavCraftImportArticleId
     * @param  array $importedVariantIds
     * @param  string $parentProductId
     * @return void
     */
    public function upsertVariants(
        Context $context,
        string $myfavCraftImportArticleId,
        array $importedVariantIds,
        string $parentProductId): void
    {
        // Get all variant entries by parent id
        $priorImportedVariants = $this->getVariantEntriesByParentId($context, $myfavCraftImportArticleId, $parentProductId);
        $priorImportedVariantsByProductIds = [];

        foreach($priorImportedVariants as $priorImportedVariant) {
            $priorImportedVariantsByProductIds[strtolower($priorImportedVariant->getProductId())] = $priorImportedVariant;
        }

        // Build upsert data.
        $upsertData = [];

        foreach($importedVariantIds as $importedVariantId => $importedVariantProductNumber) {
            $importedArticleId = Uuid::randomHex();

            if(isset($priorImportedVariantsByProductIds[strtolower($importedVariantId)])) {
                $importedArticleId = $priorImportedVariantsByProductIds[$importedVariantId]->getId();
            }

            $upsertData[] = [
                'id' => $importedArticleId,
                'myfavCraftImportArticleId' => $myfavCraftImportArticleId,
                'productId' => $importedVariantId,
                'parentProductId' => $parentProductId,
            ];
        }

        if(count($upsertData) > 0) {
            $this->importedArticleRepository->upsert($upsertData, $context);
        }
    }
}