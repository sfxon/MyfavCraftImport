<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class ImportArticleService
{
    public function __construct(
        private readonly ArticleNumberStatusService $articleNumberStatusService,
        private readonly CraftVariantService $craftVariantService,
        private readonly ImportedArticleService $importedArticleService,
        private readonly ProductService $productService,)
    {
    }

    /**
     * saveProductAndVariants
     *
     * @param  Context $context
     * @param  string $myfavCraftImportArticleId
     * @param  mixed $craftData
     * @param  array $swData
     * @return mixed
     */
    public function saveProductAndVariants(Context $context, string $myfavCraftImportArticleId, mixed $craftData, array $mainProductData): mixed
    {
        // Build array with all the variants data that is to be saved.
        if(!isset($craftData['variations']) || !is_array($craftData['variations']) || count($craftData['variations']) === 0) {
            throw new \Exception('Product has no variations.');
        }

        $shopwareVariants = null;

        foreach($craftData['variations'] as $variation) {
            if(isset($variation['skus']) && is_array($variation['skus']) && count($variation['skus']) > 0) {
                $skus = $variation['skus'];

                foreach($skus as $sku) {
                    $shopwareVariantData = $this->craftVariantService->getVariationData($context, $mainProductData, $variation, $sku);

                    if(null !== $shopwareVariantData) {
                        $shopwareVariants[] = $shopwareVariantData;
                    }
                }
            }
        }

        if($shopwareVariants === null) {
            return null;
        }

        // Check the productNumbers for the product and variants for duplicates.
        $importStatusDto = $this->articleNumberStatusService->checkForDuplicates(
            $context,
            $mainProductData,
            $shopwareVariants
        );

        if($importStatusDto->hasErrors()) {
            throw new \Exception(implode(', ', $importStatusDto->getErrorMessages()));
        }
        // Save main product.
        $productData = [];

        foreach($mainProductData as $key => $entry) {
            $productData[$key] = $entry->getValue();
        }

        // Load existing product with variants, if article already exists.
        // We need this, to cleanup data, that is not longer valid e.g. removed.
        $swMainProduct = $this->productService->getProductByProductNumber($context, $productData['productNumber']);
        $swMainProductId = null;

        if($swMainProduct !== null) {
            $swMainProductId = $swMainProduct->getId();
            // ** @var $originalSwVariantInformation array<SwVariantStatusDto>
            // Refactor: Code könnte optimiert werden, indem originalSwVariantInformation ein Dto oder Objekt wird,
            // und die Methode markAvailableVariantsAsActive ein Teil dieses Dtos/Objekts wird.
            $originalSwVariantInformation = $this->productService->getVariantsInformationByMainProductId($context, $swMainProductId);
            $originalSwVariantInformation = $this->productService->markAvailableVariantsAsActive($originalSwVariantInformation, $shopwareVariants);
            $this->productService->deactivateVariantsThatAreMarkedForDeactivation($context, $originalSwVariantInformation);
        }

        // Save main product.
        $parentProductId = $this->productService->saveProduct($context, $productData, $swMainProduct);
        $this->importedArticleService->upsert($context, $myfavCraftImportArticleId, $parentProductId, null);

        $retval = [
            'status' => 'success',
            'mainProductData' => [
                'id' => $parentProductId,
                'productNumber' => $productData['productNumber']
            ],
            'variantProductData' => []
        ];

        // Save variants.
        $importedVariantIds = $this->productService->saveVariants($context, $parentProductId, $shopwareVariants);
        $this->importedArticleService->upsertVariants($context, $myfavCraftImportArticleId, $importedVariantIds, $parentProductId);
        $this->productService->saveMainProductOptions($context, $parentProductId, $shopwareVariants);

        foreach($importedVariantIds as $id => $productNumber) {
            $retval['variantProductData'][] = [
                'id' => $id,
                'productNumber' => $productNumber
            ];
        }
        return $retval;
    }
}