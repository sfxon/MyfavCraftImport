<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class PurchasePriceImportService
{
    public function __construct(
        private readonly EntityRepository $taxRepository,
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

        if(!isset($pluginConfig['currencyId']) || $pluginConfig['currencyId'] === null) {
            throw new \Exception('Default currencyId is not configured in plugin configuration.');
        }

        $currencyId = $pluginConfig['currencyId'];

        $price = [[
            'currencyId' => $currencyId,
            'gross' => 0.0,
            'net' => 0.0,
            'linked' => true,
        ]];

        return $price;
    }
}