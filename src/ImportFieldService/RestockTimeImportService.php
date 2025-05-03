<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class RestockTimeImportService
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
        // Get config values..
        $pluginConfig = $this->systemConfigService->get('MyfavCraftImport.config');
        $restockTime = null;

        if(isset($pluginConfig['restockTime']) && is_numeric($pluginConfig['restockTime'])) {
            $restockTime = (int)$pluginConfig['restockTime'];
        }

        return $restockTime;
    }
}