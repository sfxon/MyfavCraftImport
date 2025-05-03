<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Myfav\CraftImport\Service\ImportField;
use Shopware\Core\Framework\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CraftApiResultParser
{
    public function __construct(
        private readonly ContainerInterface $container,)
    {
    }

    /**
     * getShopwareArticleDataFromCraft
     *
     * @param  mixed $context
     * @param  mixed $craftData
     * @return mixed
     */
    public function getShopwareArticleDataFromCraft(Context $context, mixed $craftData): mixed
    {
        // Fields:
        $fields = [];
        // productManufacturerId | taken from skus | $fields = new ImportField('productManufacturerId', [], new ProductManufacturerImportService());
        // unitId | wird nicht verwendet.
        $fields['taxId'] = (new ImportField(null, 'TaxImportService'));

        // productMediaImport will be done separately.
        $fields['deliveryTimeId'] = new ImportField(null, 'DeliveryTimeImportService');
        // productFeatureSetId | e.g. productFeatures -> they are imported on variant level.
        // canonicalProductId | ignored.
        $fields['cmsPageId'] = new ImportField(null, 'CmsPageImportService');

        $fields['price'] = new ImportField(['retailPrice', 'price'], 'PriceImportService');
        $fields['purchasePrice'] = new ImportField(null, 'PurchasePriceImportService');

        $fields['productNumber'] = new ImportField(['productNumber'], 'ManufacturerNumberImportService');
        $fields['restockTime'] = new ImportField(['restockTime'], 'RestockTimeImportService');

        // active | taken from skus
        // available | Shopware internal, write protected.
        $fields['isCloseout'] = new ImportField(null, 'CloseoutImportService');
        // variation | Shopware internal, write protected.
        // displayGroup | Shopware internal, write protected.
        // variantListingConfig | ignored.
        // variantRestrictions | ignored.
        $fields['manufacturerNumber'] = new ImportField(['productNumber'], 'ManufacturerNumberImportService');
        // ean | ignored
        $fields['purchaseSteps'] = new ImportField(null, 'PurchaseStepsImportService');
        // maxPurchase | ignored.
        // minPurchase | ignored.
        // purchaseUnit | ignored.
        // referenceUnit | ignored.
        // shippingFree | ignored.
        // purchasePrices | ignored | e.g. Einkaufspreise.
        // markAsTopseller | ignored.
        $fields['weight'] = new ImportField(['productWeight'], 'TakeOverNumberImportService');
        $fields['width'] = new ImportField(['productWidthStandard'], 'TakeOverNumberImportService');
        $fields['height'] = new ImportField(['productHeightStandard'], 'TakeOverNumberImportService');
        $fields['length'] = new ImportField(['productLengthStandard'], 'TakeOverNumberImportService');
        // releaseDate | ignored.

        // Properties are processed in the CustomDataParser.
        // The PropertyImportService only holds the required data.
        $fields['properties'] = new ImportField(null, 'PropertyImportService');

        // options | is part of skus.
        // tags | ignored.
        // categories | Taken from other fields.
        $fields['metaDescription'] = new ImportField(['description'], 'CraftMetaDescriptionImportService');
        $fields['name'] = new ImportField(['productName'], 'CraftProductNameImportService');
        // keywords | ignored.
        $fields['description'] = new ImportField(['description'], 'CraftDescriptionImportService');
        $fields['metaTitle'] = new ImportField(['productName'], 'CraftMetaTitleImportService');
        // packUnit | ignored.
        // packUnitPlural | ignored.
        $fields['customFields'] = new ImportField(null, 'CraftCustomFieldsImportService');
        // slotConfig | ignored.
        // customSearchKeywords | ignored.
        // stock | is part of skus, but we have to define a value for the main product, too.
        $fields['stock'] = new ImportField(null, 'CraftMainArticleStockImportService');
        $fields['visibilities'] = new ImportField(null, 'VisibilitiesImportService');

        foreach($fields as $field) {
            $field->processCraftImportService($context, $this->container, $craftData);
        }

        return $fields;
    }
}