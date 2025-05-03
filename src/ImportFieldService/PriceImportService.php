<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class PriceImportService
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

        if(!isset($pluginConfig['taxId']) || $pluginConfig['taxId'] === null) {
            throw new \Exception('Default taxId is not configured in plugin configuration.');
        }

        $currencyId = $pluginConfig['currencyId'];
        $taxId = $pluginConfig['taxId'];

        // Get default tax value.
        $tax = $this->taxRepository->search(new Criteria([$taxId]), $context)->first();

        if(null === $tax) {
            throw new \Exception('Tax with id ' . $taxId . ' could not be loaded.');
        }

        $taxRate = floatval($tax->getTaxRate());

        // Preis von Craft übernehmen.
        $priceGros = $importData['retailPrice']['price'];
        $priceGros = floatval($priceGros);

        if($priceGros === 0.0) {
            throw new \Exception('Article has got a price of 0.0');
        }

        // Netto-Preis berechnen.
        $priceNet = $priceGros;

        if($taxRate !== null && $taxRate > 0.0) {
            $priceNet = $priceNet / (1 + ($taxRate / 100));
        }

        $price = [[
            'currencyId' => $currencyId,
            'gross' => $priceGros,
            'net' => $priceNet,
            'linked' => true,
        ]];

        return $price;
    }
}