<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Myfav\CraftImport\Dto\OptionDto;
use Myfav\CraftImport\Dto\VariantDto;
use Shopware\Core\Framework\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CraftVariantService
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly PropertyService $propertyService,)
    {
    }

    /**
     * getVariationData
     *
     * @param  Context $context
     * @param  mixed $mainProductData
     * @param  mixed $variation
     * @param  mixed $skus
     * @return array
     */
    public function getVariationData(Context $context, mixed $mainProductData, mixed $variation, mixed $sku): ?VariantDto
    {
        $fields = [];
        // productNumber (= our custom product number)
        $productNameField = (new ImportField(['description'], 'TakeOverFieldValueImportService'));
        $productNameField->processCraftImportService($context, $this->container, $sku);
        $fields['name'] = $productNameField->getValue();

        // productNumber (= our custom product number)
        $productNumberField = (new ImportField(['myfavCraftSettings', 'productNumber'], 'TakeOverFieldValueImportService'));
        $productNumberField->processCraftImportService($context, $this->container, $sku);
        $fields['productNumber'] = $productNumberField->getValue();

        if($fields['productNumber'] === null || strlen(trim($fields['productNumber'])) == 0) {
            throw new \Exception('Missing product number for at least one variant.');
        }

        // manufacturerNumber (= SKU)
        $skuField = (new ImportField(['sku'], 'TakeOverFieldValueImportService'));
        $skuField->processCraftImportService($context, $this->container, $sku);
        $fields['manufacturerNumber'] = $skuField->getValue();

        // price (= Custom Price, if custom price is set.)
        $priceField = (new ImportField(['myfavCraftSettings', 'priceGros'], 'VariantPriceImportService', $mainProductData['taxId']->getValue()));
        $priceField->processCraftImportService($context, $this->container, $sku);
        $fields['price'] = $priceField->getValue();

        // price (= Purchase Price is always set to 0, but the shops need it, to operate properly.)
        $purchasePriceField = (new ImportField(null, 'PurchasePriceImportService'));
        $purchasePriceField->processCraftImportService($context, $this->container, $sku);
        $fields['purchasePrice'] = $purchasePriceField->getValue();

        // active - Only set to active, if it is marked as active and if it is available.
        $variantActiveField = (new ImportField(['myfavCraftSettings', 'activated'], 'VariantActiveImportService', $sku['availabilityGlobal']));
        $variantActiveField->processCraftImportService($context, $this->container, $sku);
        $fields['active'] = $variantActiveField->getValue();

        // isCloseout (hier immer true, im Hauptartikel immer false)
        $closeoutField = new ImportField(null, 'CloseoutImportService');
        $closeoutField->processCraftImportService($context, $this->container, $sku);
        $fields['isCloseout'] = $closeoutField->getValue();

        // stock - availabilityGlobal
        $stockField = (new ImportField(['availabilityGlobal'], 'VariantStockImportService'));
        $stockField->processCraftImportService($context, $this->container, $sku);
        $fields['stock'] = $stockField->getValue();

        // Calculate options.
        $variantDto = null;

        if($fields['active'] === true) {
            $options = $this->getOptionData($context, $variation, $sku);

            $optionsField = (new ImportField(null, 'VariantOptionsImportService'));
            $optionsField->processCraftImportService($context, $this->container, $options);
            $fields['options'] = $optionsField->getValue();

            // Create a dto, that is holding the optionIds and the calculated values.
            $variantDto = new VariantDto(
                $fields,
                $options
            );
        }

        return $variantDto;
    }

    /**
     * getOptionData
     *
     * @param  Context $context
     * @param  mixed $variation
     * @param  mixed $sku
     * @return array
     */
    private function getOptionData(Context $context, mixed $variation, mixed $sku): ?array
    {
        $size = $sku['skuSize']['webtext'];
        $colorCode = $sku['skucolor'];
        $colorName = $variation['itemColorName'];

        // Save option size.
        $options = [];
        $colorOptionGroupId = $this->propertyService->upsertPropertyGroup($context, 'Farbe');
        $options[] = new OptionDto(
            $colorOptionGroupId,
            $this->upsertOption($context, $colorOptionGroupId, $colorName, $colorCode)
        );

        $sizeOptionGroupId = $this->propertyService->upsertPropertyGroup($context, 'Größe');
        $options[] = new OptionDto(
            $sizeOptionGroupId,
            $this->upsertOption($context, $sizeOptionGroupId, $size)
        );

        return $options;
    }

    /**
     * upsertOption
     *
     * @param  mixed $context
     * @param  mixed $propertyGroupName
     * @param  mixed $optionName
     * @param  mixed $optionColorCode
     * @return string
     */
    private function upsertOption(Context $context, string $propertyGroupId, string $optionName, mixed $optionColorCode = null): string
    {
        $propertyOptionId = null;
        $propertyOption = $this->propertyService->getOptionByName($context, $propertyGroupId, $optionName);

        if(null === $propertyOption) {
            $propertyOptionId = $this->propertyService->createOption($context, $propertyGroupId, $optionName, $optionColorCode);
        } else {
            $propertyOptionId = $propertyOption->getId();
        }

        return $propertyOptionId;
    }
}