<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Myfav\CraftImport\Service\PropertyService;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

// This is for importing optional properties, not variant options.
class PropertyImportService
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
        // Die Properties werden hier nur zwischengespeichert.
        // Die finalen Werte werden in CustomPropertyImportService als OptionIds ermittelt.
        // Das ist nötig, weil mehrere Felder aus 
        // Craft-Daten und Custom-Daten ggf. gemerged werden müssen.
        /*
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
            */

        // Get the values.
        $retval['productFeatures'] = $this->getValuesFromCraftArray($importData['productFeature'], 'value');
        $retval['productFit'] = $this->getValuesFromCraftArray($importData['productFit'], 'value');

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