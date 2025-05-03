<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;

class VariantActiveImportService
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
    public function process(Context $context, mixed $importData, ?array $fieldDataIndex, mixed $availabilityGlobal): mixed
    {
        // Daten auslesen.
        $data = $importData;

        foreach ($fieldDataIndex as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                $data = false;
                break;
            }
        }

        $activated = (bool)$data;

        // Deactivated, if the stock is 0 or not set.
        $stock = (int)$availabilityGlobal;

        if($stock === 0) {
            $activated = false;
        }

        return $activated;
    }
}