<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Myfav\CraftImport\Core\Content\ImportedVereinArticle\ImportedVereinArticleEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class ImportedVereinArticleService
{
    public function __construct(
        private readonly EntityRepository $importedVereinArticleRepository,
        )
    {
    }

    /**
     * create
     *
     * @param  Context $context
     * @param  string $myfavVereinArticleId
     * @param  string $productId
     * @param  string|null $parentProductId
     * @return string
     */
    public function create(
        Context $context,
        string $myfavVereinArticleId,
        string $productId,
        ?string $parentProductId): string
    {
        $id = Uuid::randomHex();

        $data = [
            'id' => $id,
            'myfavVereinArticleId' => $myfavVereinArticleId,
            'productId' => $productId,
            'parentProductId' => $parentProductId
        ];
        $this->importedVereinArticleRepository->upsert([$data], $context);

        return $id;
    }

    /**
     * getEntryByProductNumber
     *
     * @param  Context $context
     * @param  string $productNumber
     * @return null|ImportedVereinArticleEntity
     */
    public function getEntryByProductNumber(Context $context, string $productNumber): ?ImportedVereinArticleEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addFilter(new EqualsFilter('product.productNumber', $productNumber));
        return $this->importedVereinArticleRepository->search($criteria, $context)->first();
    }

    /**
     * getEntryByIdValues
     *
     * @param  Context $context
     * @param  string $myfavVereinArticleId
     * @param  string $productId
     * @param  string|null $parentProductId
     * @return ImportedArticleEntity|null
     */
    public function getEntryByIdValues(
        Context $context,
        string $myfavVereinArticleId,
        string $productId,
        ?string $parentProductId): ?ImportedVereinArticleEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addFilter(new EqualsFilter('myfavVereinArticleId', $myfavVereinArticleId));
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->addFilter(new EqualsFilter('parentProductId', $parentProductId));
        return $this->importedVereinArticleRepository->search($criteria, $context)->first();
    }

    /**
     * getVariantEntriesByParentId
     *
     * @param  Context $context
     * @param  string $myfavVereinArticleId
     * @param  string|null $parentProductId
     * @return ImportedVereinArticleEntity|null
     */
    public function getVariantEntriesByParentId(
        Context $context,
        string $myfavVereinArticleId,
        string $parentProductId): mixed
    {
        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addFilter(new EqualsFilter('myfavVereinArticleId', $myfavVereinArticleId));
        $criteria->addFilter(new EqualsFilter('parentProductId', $parentProductId));
        return $this->importedVereinArticleRepository->search($criteria, $context);
    }

    /**
     * upsert
     *
     * @param  Context $context
     * @param  string $myfavVereinArticleId
     * @param  string $productId
     * @param  string|null $parentProductId
     * @return string
     */
    public function upsert(
        Context $context,
        string $myfavVereinArticleId,
        string $productId,
        ?string $parentProductId): string
    {
        $id = null;
        $importedProduct = $this->getEntryByIdValues($context, $myfavVereinArticleId, $productId, $parentProductId);

        if($importedProduct !== null) {
            $id = $importedProduct->getId();
        } else {
            $id = $this->create($context, $myfavVereinArticleId, $productId, $parentProductId);
        }

        return $id;
    }

    /**
     * upsertVariants
     *
     * @param  Context $context
     * @param  string $myfavVereinArticleId
     * @param  array $importedVariantIds
     * @param  string $parentProductId
     * @return void
     */
    public function upsertVariants(
        Context $context,
        string $myfavVereinArticleId,
        array $importedVariantIds,
        string $parentProductId): void
    {
        // Get all variant entries by parent id
        $priorImportedVariants = $this->getVariantEntriesByParentId($context, $myfavVereinArticleId, $parentProductId);
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
                'myfavVereinArticleId' => $myfavVereinArticleId,
                'productId' => $importedVariantId,
                'parentProductId' => $parentProductId,
            ];
        }

        if(count($upsertData) > 0) {
            $this->importedVereinArticleRepository->upsert($upsertData, $context);
        }
    }
}