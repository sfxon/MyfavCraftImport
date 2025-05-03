<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Shopware\Core\Framework\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomDataParser
{
    public function __construct(
        private readonly ContainerInterface $container,)
    {
    }

    /**
     * setCustomArticleData
     *
     * @param  Context $context
     * @param  mixed $swData
     * @param  mixed $customProductSettings
     * @return mixed
     */
    public function setCustomArticleData(Context $context, mixed &$fields, mixed $customProductSettings): mixed
    {
        // productManufacturerId | taken from skus | $fields = new ImportField('productManufacturerId', [], new ProductManufacturerImportService());
        // unitId | ignored.

        // taxId
        CustomImportFieldProcessor::updateField(
            $context,
            $fields, // Fields that have already been populated with import data from the original data source.
            'taxId', // Fieldname, that should be updated.
            $this->container, // Symfony Container for fabric processing.
            $customProductSettings, // Array with new data, that may override existing data.
            ['updateTaxId'], // Index-Path of the field in customProductSettings, that defines if the data should be updated.
            ['customTaxId'], // Index-Path of the field in customProductSettings, that is holding the new value.
            'TakeOverNumberImportService' // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
        );

        // productMediaImport will be done separately.

        // deliveryTimeId | Taken from Main Product, which takes the id from the plugin config.
        // productFeatureSetId | ignored.
        // canonicalProductId | ignored.
        // cmsPageId | Taken from Main Product, which takes the id from the plugin config.
        
        // price
        $priceData = [
            'craftPrice' => $fields['price']->getValue(),
            'customProductSettings' => $customProductSettings
        ];
        CustomImportFieldProcessor::updateField(
            $context,
            $fields, // Fields that have already been populated with import data from the original data source.
            'price', // Fieldname, that should be updated.
            $this->container, // Symfony Container for fabric processing.
            $priceData, // Array with new data, that may override existing data.
            null, // Index-Path of the field in customProductSettings, that defines if the data should be updated.
            null, // Index-Path of the field in customProductSettings, that is holding the new value.
            'CustomPriceImportService' // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
        );

        // productNumber
        CustomImportFieldProcessor::updateField(
            $context,
            $fields, // Fields that have already been populated with import data from the original data source.
            'productNumber', // Fieldname, that should be updated.
            $this->container, // Symfony Container for fabric processing.
            $customProductSettings, // Array with new data, that may override existing data.
            ['updateProductNumber'], // Index-Path of the field in customProductSettings, that defines if the data should be updated.
            ['customProductNumber'], // Index-Path of the field in customProductSettings, that is holding the new value.
            'TakeOverFieldValueImportService' // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
        );

        // restockTime | ignored.
        // active | taken from skus
        // available | Shopware internal, write protected.
        // isCloseout'] | Already set from craftApiResultParser.
        // variation | Shopware internal, write protected.
        // displayGroup | Shopware internal, write protected.
        // variantListingConfig | ignored.
        // variantRestrictions | ignored.
        // manufacturerNumber | Already set from craftApiResultParser.
        // ean | ignored
        // purchaseSteps | Already set from craftApiResultParser.
        // maxPurchase | ignored.
        // minPurchase | ignored.
        // purchaseUnit | ignored.
        // referenceUnit | ignored.
        // shippingFree | ignored.
        // purchasePrices | ignored | e.g. Einkaufspreise.
        // markAsTopseller | ignored.
        // weight | Already set from craftApiResultParser.
        // width | Already set from craftApiResultParser.
        // height | Already set from craftApiResultParser.
        // length | Already set from craftApiResultParser.
        // releaseDate | ignored.
        
        // properties
        $propertyData = [
            'craftProperties' => $fields['properties']->getValue(),
            'customProductSettings' => $customProductSettings
        ];

        CustomImportFieldProcessor::updateField(
            $context,
            $fields, // Fields that have already been populated with import data from the original data source.
            'properties', // Fieldname, that should be updated.
            $this->container, // Symfony Container for fabric processing.
            $propertyData, // Array with new data, that may override existing data.
            null, // Index-Path of the field in customProductSettings, that defines if the data should be updated.
            null, // Index-Path of the field in customProductSettings, that is holding the new value.
            'CustomPropertyImportService' // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
        );

        // options | is part of skus.
        // tags | ignored.

        // categories
        CustomImportFieldProcessor::updateField(
            $context,
            $fields, // Fields that have already been populated with import data from the original data source.
            'categories', // Fieldname, that should be updated.
            $this->container, // Symfony Container for fabric processing.
            $customProductSettings, // Array with new data, that may override existing data.
            ['updateProductCategories'], // Index-Path of the field in customProductSettings, that defines if the data should be updated.
            ['customProductCategories'], // Index-Path of the field in customProductSettings, that is holding the new value.
            'TakeOverFieldValueImportService' // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
        );

        // metaDescription
        CustomImportFieldProcessor::updateField(
            $context,
            $fields, // Fields that have already been populated with import data from the original data source.
            'metaDescription', // Fieldname, that should be updated.
            $this->container, // Symfony Container for fabric processing.
            $customProductSettings, // Array with new data, that may override existing data.
            ['updateProductDescription'], // Index-Path of the field in customProductSettings, that defines if the data should be updated.
            ['customProductDescription'], // Index-Path of the field in customProductSettings, that is holding the new value.
            'CraftMetaDescriptionImportService' // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
        );

        // name
        CustomImportFieldProcessor::updateField(
            $context,
            $fields, // Fields that have already been populated with import data from the original data source.
            'name', // Fieldname, that should be updated.
            $this->container, // Symfony Container for fabric processing.
            $customProductSettings, // Array with new data, that may override existing data.
            ['updateProductName'], // Index-Path of the field in customProductSettings, that defines if the data should be updated.
            ['customProductName'], // Index-Path of the field in customProductSettings, that is holding the new value.
            'CraftProductNameImportService' // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
        );

        // keywords | ignored.

        // description
        CustomImportFieldProcessor::updateField(
            $context,
            $fields, // Fields that have already been populated with import data from the original data source.
            'description', // Fieldname, that should be updated.
            $this->container, // Symfony Container for fabric processing.
            $customProductSettings, // Array with new data, that may override existing data.
            ['updateProductDescription'], // Index-Path of the field in customProductSettings, that defines if the data should be updated.
            ['customProductDescription'], // Index-Path of the field in customProductSettings, that is holding the new value.
            'CraftDescriptionImportService' // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
        );


        // metaTitle
        CustomImportFieldProcessor::updateField(
            $context,
            $fields, // Fields that have already been populated with import data from the original data source.
            'metaTitle', // Fieldname, that should be updated.
            $this->container, // Symfony Container for fabric processing.
            $customProductSettings, // Array with new data, that may override existing data.
            ['updateProductName'], // Index-Path of the field in customProductSettings, that defines if the data should be updated.
            ['customProductName'], // Index-Path of the field in customProductSettings, that is holding the new value.
            'CraftMetaTitleImportService' // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
        );

        // packUnit | ignored.
        // packUnitPlural | ignored.
        // customFields'] = new ImportField($this->container, $craftData, null, 'CraftCustomFieldsImportService');
        CustomImportFieldProcessor::updateField(
            $context,
            $fields, // Fields that have already been populated with import data from the original data source.
            'customFields', // Fieldname, that should be updated.
            $this->container, // Symfony Container for fabric processing.
            $customProductSettings, // Array with new data, that may override existing data.
            null, // Index-Path of the field in customProductSettings, that defines if the data should be updated.
            null, // Index-Path of the field in customProductSettings, that is holding the new value.
            'CustomCustomFieldsImportService' // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
        );

        // slotConfig | ignored.
        // customSearchKeywords | ignored.
        // stock | is part of skus.
        return $fields;
    }
}