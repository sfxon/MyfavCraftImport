<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;

class CraftDescriptionImportService
{
    /**
     * process
     *
     * @param  mixed $importData
     * @param  ?array $fieldDataIndex
     * @return mixed
     */
    public function process(Context $context, mixed $importData, ?array $fieldDataIndex): mixed
    {
        // Daten auslesen.
        $data = $importData;

        foreach ($fieldDataIndex as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                $data = null;
                break;
            }
        }

        if($data !== null) {
            $data = nl2br($data);
        }

        return $data;
    }
}