<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Myfav\CraftImport\Service\PropertyService;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

// This is for importing optional properties, not variant options.
class CustomPropertyImportService
{
    public function __construct(
        private readonly PropertyService $propertyService,
        private readonly SystemConfigService $systemConfigService,)
    {
    }

    /**
     * process
     *
     * @param  mixed $importData
     * @param  ?array $fieldDataIndex
     * @return mixed
     */
    public function process(Context $context, mixed $importData, ?array $fieldDataIndex): mixed
    {
        $propertyGroupIdBesonderheiten = null;
        $propertyGroupIdPassform = null;

        // Get configuration.
        $pluginConfig = $this->systemConfigService->get('MyfavCraftImport.config');

        if(isset($pluginConfig['productFeaturePropertyId'])) {
            $propertyGroupIdBesonderheiten = $pluginConfig['productFeaturePropertyId'];
        }

        if(isset($pluginConfig['productFitPropertyId'])) {
            $propertyGroupIdPassform = $pluginConfig['productFitPropertyId'];
        }

        // Get the values from the craft data first. This data may be overwritten in the next step.
        $customProductSettings = $importData['customProductSettings'];
        $productFeatures = $importData['craftProperties']['productFeatures'];
        $productFit = $importData['craftProperties']['productFit'];

        // Fetch input values for features.
        if(isset($customProductSettings['updateProductFeatures']) && $customProductSettings['updateProductFeatures'] === true && isset($customProductSettings['customProductFeatures'])) {
            $updateProductFeatures = $customProductSettings['updateProductFeatures'];
            $customProductFeatures = $customProductSettings['customProductFeatures'];
            $customProductFeatures = trim($customProductFeatures);
            $customProductFeaturesSet = false;

            if($updateProductFeatures === true && strlen($customProductFeatures) > 0) {
                $customProductFeatures = explode("\n", $customProductFeatures);
                $features = [];

                foreach($customProductFeatures as $tmpFeature) {
                    $tmpFeature = trim($tmpFeature);

                    if(strlen($tmpFeature) > 0) {
                        $features[] = $tmpFeature;
                        $customProductFeaturesSet = true;
                    }
                }

                if($customProductFeaturesSet) {
                    $productFeatures = $features;
                }
            }
        }

        // Fetch input values for ProductFit.
        if(isset($customProductSettings['updateProductFit']) && $customProductSettings['updateProductFit'] === true && isset($customProductSettings['customProductFit'])) {
            $updateProductFit = $customProductSettings['updateProductFit'];
            $customProductFit = $customProductSettings['customProductFit'];
            $customProductFit = trim($customProductFit);

            if($updateProductFit && strlen($customProductFit) > 0) {
                $productFit = [$customProductFit];
            }
        }

        // Get the property-options by groupId and name.
        $retval = null;

        if(
            (is_array($productFeatures) && count($productFeatures) > 0) ||
            (is_array($productFit) && count($productFit) > 0))
        {
            $retval = [];

            if($propertyGroupIdBesonderheiten !== null && is_array($productFeatures) && count($productFeatures) > 0) {
                $retval = $this->getOrCreatePropertyOptions($context, $propertyGroupIdBesonderheiten, $productFeatures);
            }

            if($propertyGroupIdPassform !== null && is_array($productFit) && count($productFit) > 0) {
                $productFitIds = $this->getOrCreatePropertyOptions($context, $propertyGroupIdPassform, $productFit);
                $retval = array_merge($retval, $productFitIds);
            }
        }

        return $retval;
    }

    /**
     * getValuesFromCraftArray
     *
     * @param  mixed $array
     * @param  mixed $key
     * @return array
     */
    private function getValuesFromCraftArray($array, $key): array
    {
        $retval = [];

        if(is_array($array)) {
            if(isset($array[$key])) {
                $retval[] = $array[$key];
            } else {
                foreach($array as $arrayEntry) {
                    if(isset($arrayEntry[$key])) {
                        $retval[] = $arrayEntry[$key];
                    }
                }
            }
        }

        return $retval;
    }

    /**
     * getOrCreatePropertyOptions
     *
     * @param  mixed $context
     * @param  mixed $propertyGroupIdBesonderheiten
     * @param  mixed $productFeatures
     * @return array
     */
    private function getOrCreatePropertyOptions(Context $context, string $propertyGroupId, array $propertyOptionNames): array
    {
        $retval = [];

        foreach($propertyOptionNames as $propertyOptionName) {
            $propertyOptionId = null;
            $propertyOption = $this->propertyService->getOptionByName($context, $propertyGroupId, $propertyOptionName);

            if(null === $propertyOption) {
                $propertyOptionId = $this->propertyService->createOption($context, $propertyGroupId, $propertyOptionName);
            } else {
                $propertyOptionId = $propertyOption->getId();
            }

            $retval[] = $propertyOptionId;
        }

        return $retval;
    }
}