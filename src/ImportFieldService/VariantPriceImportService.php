<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class VariantPriceImportService
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
    public function process(Context $context, mixed $importData, ?array $fieldDataIndex, mixed $additionalData): mixed
    {
        // Get config values..
        $pluginConfig = $this->systemConfigService->get('MyfavCraftImport.config');

        if(!isset($pluginConfig['currencyId']) || $pluginConfig['currencyId'] === null) {
            throw new \Exception('Default currencyId is not configured in plugin configuration.');
        }

        $currencyId = $pluginConfig['currencyId'];

        // Get default tax value.
        $taxId = $additionalData;
        $tax = $this->taxRepository->search(new Criteria([$taxId]), $context)->first();

        if(null === $tax) {
            throw new \Exception('Tax with id ' . $taxId . ' could not be loaded.');
        }

        $taxRate = floatval($tax->getTaxRate());

        // Daten auslesen.
        $priceGros = 0.0;
        $useCustomPrice = $importData['myfavCraftSettings']['useCustomPrice'];

        if($useCustomPrice === false) {
            // Wenn einfach der Craft Preis der Variante übernommen werden soll.
            $priceGros = $importData['retailPrice']['price'];

            if(!is_float($priceGros)) {
                $priceGros = str_replace(',', '.', $priceGros);
                $priceGros = floatval($priceGros);
            }
        } else {
            // Wenn ein Custom Preis oder eine Preis-Berechnung verwendet  werden soll.
            $usePercentualDiscount = $importData['myfavCraftSettings']['usePercentualDiscount'];

            if($usePercentualDiscount === false) {
                // Use custom price.
                $priceGros = $importData['myfavCraftSettings']['priceGros'];
                $priceGros = str_replace(',', '.', $priceGros);
                $priceGros = floatval($priceGros);
            } else {
                // User percentual discounted price.
                $priceGros = $importData['retailPrice']['price'];
                $discountInPercent = $importData['myfavCraftSettings']['discountInPercent'];
                $discountInPercent = str_replace(',', '.', $discountInPercent);
                $discountInPercent = floatval($discountInPercent);

                if($priceGros !== 0.0 && $discountInPercent !== 0.0) {
                    $priceGros = $priceGros - ($priceGros / 100 * $discountInPercent);
                    $priceGros = round($priceGros, 2);
                }
            }
        }

        // Build price data array for shopware import.
        if(!is_float($priceGros)) {
            $priceGros = str_replace(',', '.', $priceGros);
            $priceGros = floatval($priceGros);
        }
        
        if($priceGros === 0.0) {
            throw new \Exception('Article ' . $importData['myfavCraftSettings']['productNumber'] . ' has got a price of 0.0');
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