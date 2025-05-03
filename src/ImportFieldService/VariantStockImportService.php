<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;

class VariantStockImportService
{
    public function __construct()
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
        $stockValue = (int)$importData['availabilityGlobal'];

        if(
            isset($importData['myfavCraftSettings']) && 
            isset($importData['myfavCraftSettings']['useCustomStock']) &&
            $importData['myfavCraftSettings']['useCustomStock'] === true &&
            isset($importData['myfavCraftSettings']['updateCustomStockNow']) &&
            $importData['myfavCraftSettings']['updateCustomStockNow'] === true &&
            isset($importData['myfavCraftSettings']['customStockValue'])
        ) {
            $stockValue = (int)$importData['myfavCraftSettings']['customStockValue'];
        }

        return  $stockValue;
    }
}