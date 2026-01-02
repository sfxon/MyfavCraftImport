<?php declare(strict_types=1);

namespace Myfav\CraftImport\ImportFieldService;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CloseoutDisabledImportService
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
        return false;
    }
}