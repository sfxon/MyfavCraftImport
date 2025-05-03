<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class DeliveryTimeImportService
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
        $pluginConfig = $this->systemConfigService->get('MyfavCraftImport.config');

        if(!isset($pluginConfig['deliveryTimeId']) || $pluginConfig['deliveryTimeId'] === null) {
            throw new \Exception('Default deliveryTimeId is not configured in plugin configuration.');
        }

        return $pluginConfig['deliveryTimeId'];
    }
}