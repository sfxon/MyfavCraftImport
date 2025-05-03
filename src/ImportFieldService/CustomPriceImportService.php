<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CustomPriceImportService
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

        // Use customTaxId if a different tax class has been selected.
        if(
            isset($importData['customProductSettings']['updateTaxId']) &&
            $importData['customProductSettings']['updateTaxId'] === true &&
            isset($importData['customProductSettings']['customTaxId']) &&
            $importData['customProductSettings']['customTaxId'] !== null &&
            $importData['customProductSettings']['customTaxId'] !== ''
        ) {
            $taxId = $importData['customProductSettings']['customTaxId'];
        }

        // Get default tax value.
        $tax = $this->taxRepository->search(new Criteria([$taxId]), $context)->first();

        if(null === $tax) {
            throw new \Exception('Tax with id ' . $taxId . ' could not be loaded.');
        }

        $taxRate = floatval($tax->getTaxRate());

        // Daten auslesen.
        $updateProductPriceGros = (bool)$importData['customProductSettings']['updateProductPriceGros'];

        // Wenn einfach der Craft Preis übernommen werden soll, können wir den bereits geparst zurückgeben.
        if($updateProductPriceGros === false) {
            return $importData['craftPrice'];
        }

        // Build price data array for shopware import.
        $priceGros = 0.0;
        $usePercentualDiscount = $importData['customProductSettings']['usePercentualDiscount'];

        if($usePercentualDiscount === false) {
            // Use custom price.
            $priceGros = $importData['customProductSettings']['customProductPriceGros'];
            $priceGros = str_replace(',', '.', $priceGros);
            $priceGros = floatval($priceGros);
        } else {
            // User percentual discounted price.
            $priceGros = $importData['craftPrice'][0]['gross'];
            $discountInPercent = $importData['customProductSettings']['discountInPercent'];
            $discountInPercent = str_replace(',', '.', $discountInPercent);
            $discountInPercent = floatval($discountInPercent);

            if($priceGros !== 0.0 && $discountInPercent !== 0.0) {
                $priceGros = $priceGros - ($priceGros / 100 * $discountInPercent);
                $priceGros = round($priceGros, 2);
            }
        }

        if($priceGros === 0.0) {
            throw new \Exception('Article ' . $importData['customProductSettings']['customProductNumber'] . ' has got a price of 0.0');
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