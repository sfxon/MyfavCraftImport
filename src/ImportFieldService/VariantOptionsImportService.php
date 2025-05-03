<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;

class VariantOptionsImportService
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
        $configuratorSettings = [];

        foreach($importData as $option) {
            $configuratorSettings[] = [
                'id' => $option->getPropertyGroupOptionId()
            ];
        }

        return $configuratorSettings;
    }
}