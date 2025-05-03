<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class VisibilitiesImportService
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

        if(!isset($pluginConfig['salesChannels']) || $pluginConfig['salesChannels'] === null) {
            throw new \Exception('Default salesChannels are not configured in plugin configuration.');
        }

        $visibilities = [];

        foreach($pluginConfig['salesChannels'] as $salesChannelId) {
            $visibilities[] = [
                'salesChannelId' => $salesChannelId,
                'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL
            ];
        }

        return $visibilities;
    }
}