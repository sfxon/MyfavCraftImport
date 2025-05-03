<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CustomCustomFieldsImportService
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,)
    {
    }

    /**
     * process
     * This is not the best way to do this for custom fields.
     * If you ever need to have multiple custom fields, where only some are to be overwritten,
     * this would have to be refactored. The values then would have to be merged in already existing values.
     * To this point, the current approach is enough.
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

        if(
            !isset($importData['updateProductCustomFieldForFabrics']) ||
            $importData['updateProductCustomFieldForFabrics'] !== true ||
             !isset($importData['customProductCustomFieldForFabrics'])) {
            return null;
        }

        $fabrics = $importData['customProductCustomFieldForFabrics'];
        $fabrics = trim($fabrics);

        if(strlen($fabrics) === 0) {
            return null;
        }

        $customFields[$productCustomFieldForFabrics] = $fabrics;

        return $customFields;
    }
}