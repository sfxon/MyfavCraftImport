<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CraftProductSaveService
{
    public function __construct(
        private readonly CraftApiResultParser $craftApiResultParser,
        private readonly ImportArticleService $importArticleService,
        private readonly MyfavCraftImportArticleService $myfavCraftImportArticleService,
        private readonly CustomDataParser $customDataParser,
        private readonly SystemConfigService $systemConfigService,)
    {
    }

    /**
     * saveProduct
     *
     * @param  Context $context
     * @param  mixed $craftData
     * @param  mixed $customProductSettings
     * @param  mixed $syncProduct
     * @return mixed
     */
    public function saveProduct(Context $context, mixed $craftData, mixed $customProductSettings, bool $syncProduct):mixed
    {
        // Save input data for later processing.
        $craftProductNumber = $craftData['productNumber'];
        $myfavCraftImportArticleId = $this->myfavCraftImportArticleService->saveCustomData(
            $context,
            $craftProductNumber,
            $craftData,
            $customProductSettings
        );

        // Parse input data. This could also be done by the values that have been saved in the method above,
        // to reconstruct an import, even if the data is not longer provided by the craft api.
        $swData = $this->craftApiResultParser->getShopwareArticleDataFromCraft($context, $craftData);
        $swData = $this->customDataParser->setCustomArticleData($context, $swData, $customProductSettings);

        if($syncProduct === true) {
            return $this->importArticleService->saveProductAndVariants($context, $myfavCraftImportArticleId, $craftData, $swData);
        }

        return null;
    }
}