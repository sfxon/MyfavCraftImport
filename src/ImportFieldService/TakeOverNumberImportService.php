<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class TakeOverNumberImportService
{
    public function __construct()
    {
    }

    /**
     * process
     *
     * @param  Context $context
     * @param  mixed $importData
     * @param  ?array $fieldDataIndex
     * @param  mixed $additionalData
     * @param  mixed $defaultData
     * @return mixed
     */
    public function process(Context $context, mixed $importData, ?array $fieldDataIndex, mixed $additionalData = null, mixed $defaultData = null): mixed
    {
        // Daten auslesen.
        $data = $importData;

        foreach ($fieldDataIndex as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                $data = $defaultData;
                break;
            }
        }

        return $data;
    }
}