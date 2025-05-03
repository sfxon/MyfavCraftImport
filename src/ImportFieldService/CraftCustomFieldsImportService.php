<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CraftCustomFieldsImportService
{
    public function __construct(
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
        // Get configuration.
        $customFields = [];
        $productCustomFieldForFabrics = null;
        $pluginConfig = $this->systemConfigService->get('MyfavCraftImport.config');

        if(isset($pluginConfig['productCustomFieldForFabrics'])) {
            $productCustomFieldForFabrics = $pluginConfig['productCustomFieldForFabrics'];
        }

        if(null === $productCustomFieldForFabrics) {
            return null;
        }

        if(!isset($importData['productFabrics'])) {
            return null;
        }

        $customFields[$productCustomFieldForFabrics] = $importData['productFabrics'];

        return $customFields;
    }
}