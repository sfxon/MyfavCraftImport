<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Myfav\CraftImport\Dto\VariantInformationDto;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class ProductService
{
    public function __construct(
        private readonly EntityRepository $productCategoryRepository,
        private readonly EntityRepository $productConfiguratorSettingRepository,
        private readonly EntityRepository $productPropertyRepository,
        private readonly EntityRepository $productOptionRepository,
        private readonly EntityRepository $productVisibilityRepository,
        private readonly EntityRepository $productRepository,
        private readonly PropertyService $propertyService,
    )
    {
    }

    /**
     * getById
     *
     * @param  Context $context
     * @param  string $productId
     * @return ProductEntity
     */
    public function getById(Context $context, string $productId): ?ProductEntity
    {
        $criteria = new Criteria([$productId]);
        $criteria->addAssociation('categories');
        $criteria->addAssociation('options');
        $criteria->addAssociation('properties');
        $criteria->addAssociation('visibilities');
        $criteria->addAssociation('configuratorSettings');
        $criteria->addAssociation('media');
        return $this->productRepository->search($criteria, $context)->first();
    }
    
    /**
     * getProductByProductNumber
     *
     * @param  Context $context
     * @param  string $productNumber
     * @return ProductEntity
     */
    public function getProductByProductNumber(Context $context, string $productNumber): ?ProductEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociation('categories');
        $criteria->addAssociation('options');
        $criteria->addAssociation('properties');
        $criteria->addAssociation('visibilities');
        $criteria->addAssociation('configuratorSettings');
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
        return $this->productRepository->search($criteria, $context)->first();
    }

    /**
     * getVariantsInformationByMainProductId
     *
     * @param  Context $context
     * @param  string $mainProductId
     * @return array
     */
    public function getVariantsInformationByMainProductId(Context $context, string  $mainProductId): array
    {
        $retval = [];

        $criteria = new Criteria();
        $criteria->addFilter(new  EqualsFilter('parentId',  $mainProductId));
        $criteria->setLimit(10);

        $iterator = new RepositoryIterator($this->productRepository, $context, $criteria);

        while(($result = $iterator->fetch()) !== null) {
            $products = $result->getEntities();

            foreach($products as $product) {
                $retval[$product->getProductNumber()] = new VariantInformationDto(
                    $product->getId(),
                    $product->getProductNumber(),
                    false, // set active state as default to 0
                );
            }
        }

        return $retval;
    }

    /**
     * deactivateVariantsThatAreMarkedForDeactivation
     *
     * @param  Context $context
     * @param  array $originalSwVariantInformation
     * @return void
     */
    public function deactivateVariantsThatAreMarkedForDeactivation(Context $context, array $originalSwVariantInformation): void
    {
        $data = [];

        foreach($originalSwVariantInformation as $entry) {
            if(!$entry->isActive()) {
                $data[] = [
                    'id' => $entry->getProductId(),
                    'active' => false
                ];
            }
        }

        if(count($data) === 0) {
            return;
        }

        $this->productRepository->upsert($data, $context);
    }

    /**
     * markAvailableVariantsAsActive
     *
     * @param  array $originalSwVariantInformation
     * @param  array $shopwareVariants
     * @return array
     */
    public function markAvailableVariantsAsActive(array $originalSwVariantInformation, array $shopwareVariants): array
    {
        foreach($shopwareVariants as $shopwareVariant) {
            $shopwareVariantFields = $shopwareVariant->getShopwareFieldsForVariant();
            $shopwareVariantProductNumber = $shopwareVariantFields['productNumber'];

            if(isset($originalSwVariantInformation[$shopwareVariantProductNumber])) {
                $originalSwVariantInformation[$shopwareVariantProductNumber]->setActive($shopwareVariantFields['active']);
            }//  else {
            //    $originalSwVariantInformation[$shopwareVariantProductNumber]->setActive(false);
            //}
        }

        return $originalSwVariantInformation;
    }

    /**
     * saveProduct
     *
     * @param  Context $context
     * @param  array $productData
     * @param  ProductEntity|null $productEntity
     * @return string
     */
    public function saveProduct(Context $context, array $productData, ?ProductEntity $product): string
    {
        $id = Uuid::randomHex();

        $product = $this->getProductByProductNumber($context, $productData['productNumber']);

        if($product !== null) {
            $id = $product->getId();
        }

        $productData['id'] = $id;

        // m:m Entities have to be saved on a special way.
        $categories = null;
        $options = null;
        $properties = null;
        $visibilities = null;

        if(isset($productData['categories'])) {
            $categories = $productData['categories'];
            unset($productData['categories']);
        }

        if(isset($productData['properties'])) {
            $properties = $productData['properties'];
            unset($productData['properties']);
        }

        if(isset($productData['visibilities'])) {
            $visibilities = $productData['visibilities'];
            unset($productData['visibilities']);
        }

        $this->productRepository->upsert([$productData], $context);

        $this->updateCategories($context, $id, $product, $categories);
        $this->updateProperties($context, $id, $product, $properties);
        $this->updateVisibilities($context, $id, $product, $visibilities);
        $this->removeExistingProductOptions($context, $product);

        return $id;
    }

    /**
     * saveVariants
     *
     * @param  mixed $context
     * @param  mixed $parentProductId
     * @param  mixed $shopwareVariants
     * @return array
     */
    public function saveVariants(
        Context $context,
        string $parentProductId,
        array $shopwareVariants): array
    {
        $variantIds = [];
        $productNumbers = [];
        $variantsForSaving = [];

        foreach($shopwareVariants as $variant) {
            $variantData = $variant->getShopwareFieldsForVariant();
            $productNumbers[] = $variantData['productNumber'];
        }

        $productNumberMapping = $this->fetchProductIdMappingByProductNumbers($context, $productNumbers);

        // Set ids in product.
        foreach($shopwareVariants as $index => $variant) {
            $variantData = $variant->getShopwareFieldsForVariant();

            if($variantData['active'] !== true) {
                continue;
            }

            $productNumber = $variantData['productNumber'];
            $variantId = Uuid::randomHex();
            
            if(isset($productNumberMapping[$productNumber])) {
                $variantId = $productNumberMapping[$productNumber];
            }

            $shopwareVariants[$index]->setVariantField('id', $variantId);
            $variantIds[$variantId] = $productNumber;
            $shopwareVariants[$index]->setVariantField('parentId', $parentProductId);
            $variantsForSaving[] = $shopwareVariants[$index]->getShopwareFieldsForVariant();
        }

        $this->productRepository->upsert($variantsForSaving, $context);

        return $variantIds;
    }

    /**
     * saveMainProductOptions
     *
     * @param  mixed $context
     * @param  mixed $productId
     * @param  mixed $shopwareVariants
     * @return void
     */
    public function saveMainProductOptions(
        Context $context,
        string $productId,
        mixed $shopwareVariants): void
    {
        $optionIds = [];
        $setOptionIds = [];

        foreach($shopwareVariants as $variant) {
            $variantOptions = $variant->getOptionIds();

            foreach($variantOptions as $variantOption) {
                $variantOptionId = $variantOption->getPropertyGroupOptionId();

                if(isset($setOptionIds[$variantOptionId])) {
                    continue;
                }
                
                $optionIds[] = [
                    'optionId' => $variantOptionId
                ];
                $setOptionIds[$variantOptionId] = true;
            }
        }

        $data = [
            'id' => $productId,
            'configuratorSettings' => $optionIds,
            'variantListingConfig' => [
                'extensions' => [],
                'displayParent' => true,
                'mainVariantId' => null,
                // TODO: This could be changed, because in variantOptions we also have the propertyGroupIds.
                'configuratorGroupConfig' => [
                    [
                        'id' => $this->propertyService->getPropertyGroupByName($context, 'Größe')->getId(),
                        'expressionForListings' => false,
                        'representation' => 'box',
                    ],
                    [
                        'id' => $this->propertyService->getPropertyGroupByName($context, 'Farbe')->getId(),
                        'expressionForListings' => false,
                        'representation' => 'box',
                    ],
                ]
            ],
        ];

        $this->productRepository->update([
            $data
        ], $context);
    }

    /**
     * @param string[] $productNumbers
     * @return array<string, string> Mapping Produktnummer => ID
     */
    public function fetchProductIdMappingByProductNumbers(Context $context, array $productNumbers): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productNumber', $productNumbers));
        $criteria->setLimit(count($productNumbers));

        $products = $this->productRepository->search($criteria, $context);

        $mapping = [];

        /** @var ProductEntity $product */
        foreach ($products as $product) {
            $mapping[$product->getProductNumber()] = $product->getId();
        }

        return $mapping;
    }

    /**
     * removeExistingProductOptions
     *
     * @param  Context $context
     * @param  ProductEntity|null $product
     * @return void
     */
    public function removeExistingProductOptions(Context $context, ?ProductEntity $product): void
    {
        if($product === null || $product->getConfiguratorSettings() === null) {
            return;
        }

        $deleteData = [];

        foreach($product->getConfiguratorSettings() as $configuratorSetting) {
            $deleteData[] = [
                'id' => $configuratorSetting->getId()
            ];
        }

        if(count($deleteData) > 0) {
            $this->productConfiguratorSettingRepository->delete($deleteData, $context);
        }
    }

    /**
     * updateCategories
     *
     * @param  Context $context
     * @param  string $prdouctId
     * @param  ProductEntity|null $product
     * @param  array|null $categories
     * @return void
     */
    public function updateCategories(Context $context, string $productId, ?ProductEntity $product, ?array $categories): void
    {
        // Delete existing mapping entities, if there are any.
        if($product !== null && $product->getCategories() !== null) {
            $deleteData = [];
            
            foreach($product->getCategories() as $category) {
                $deleteData[] = [
                    'productId' => $product->getId(),
                    'categoryId' => $category->getId()
                ];
            }

            if(count($deleteData) > 0) {
                $this->productCategoryRepository->delete($deleteData, $context);
            }
        }

        // Save category mapping, if it is set.
        if($categories !== null && count($categories) > 0) {
            $categoryData = [];

            foreach($categories as $category) {
                $categoryData[] = [
                    'id' => $category
                ];
            }

            $this->productRepository->update([
                [
                    'id' => $productId,
                    'categories' => $categoryData
                ],
            ], $context);
        }
    }

    /**
     * updateProperties
     *
     * @param  Context $context
     * @param  string $productId
     * @param  ProductEntity|null $product
     * @param  array|null $properties
     * @return void
     */
    public function updateProperties(Context $context, string $productId, ?ProductEntity $product, ?array $properties): void
    {
        // Delete existing mapping entities, if there are any.
        if($product !== null && $product->getProperties() !== null) {
            $deleteData = [];
            
            foreach($product->getProperties() as $property) {
                $deleteData[] = [
                    'productId' => $product->getId(),
                    'optionId' => $property->getId()
                ];
            }

            if(count($deleteData) > 0) {
                $this->productPropertyRepository->delete($deleteData, $context);
            }
        }

        // Save category mapping, if it is set.
        if($properties !== null && count($properties) > 0) {
            $propertyData = [];

            foreach($properties as $property) {
                $propertyData[] = [
                    'id' => $property
                ];
            }

            $this->productRepository->update([
                [
                    'id' => $productId,
                    'properties' => $propertyData
                ],
            ], $context);
        }
    }

    /**
     * updateVisibilities
     *
     * @param  Context $context
     * @param  string $productId
     * @param  ProductEntity|null $product
     * @param  array|null $visibilities
     * @return void
     */
    public function updateVisibilities(Context $context, string $productId, ?ProductEntity $product, ?array $visibilities): void
    {
        // Delete existing mapping entities, if there are any.
        if($product !== null && $product->getProperties() !== null) {
            $deleteData = [];
            
            foreach($product->getVisibilities() as $visibility) {
                $deleteData[] = [
                    'id' => $visibility->getId(),
                ];
            }

            if(count($deleteData) > 0) {
                $this->productVisibilityRepository->delete($deleteData, $context);
            }
        }

        $this->productRepository->update([
            [
                'id' => $productId,
                'visibilities' => $visibilities
            ],
        ], $context);
    }
}