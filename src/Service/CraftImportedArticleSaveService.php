<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Myfav\CraftImport\Service\ArticleNumberStatusService;
use Myfav\CraftImport\Dto\ImportStatusDto;
use Shopware\Core\Framework\Context;

class CraftImportedArticleSaveService
{
    public function __construct(
        private readonly ArticleNumberStatusService $articleNumberStatusService,
        private readonly CraftApiResultParser $craftApiResultParser,
        private readonly CustomDataParser $customDataParser,
        private readonly ImportVereinArticleService $importVereinArticleService,
        private readonly MyfavCraftImportArticleService $myfavCraftImportArticleService,
        private readonly MyfavVereinArticleService $myfavVereinArticleService,)
    {
    }

    /**
     * saveProduct
     */
    public function saveProduct(
        Context $context,
        mixed $myfavVereinId,
        mixed $myfavCraftImportArticleId,
        mixed $customProductSettings,
        mixed $overriddenCustomProductSettings,
        mixed $variations): ImportStatusDto
    {
        // Make sure, that article number of main article and variants do not already exist!
        $productNumbersToCheck = [];

        if(isset($customProductSettings['customProductNumber'])) {
            $productNumbersToCheck[] = $customProductSettings['customProductNumber'];
        }

        if(is_array($variations)) {
            foreach($variations as $variation) {
                if(isset($variation['skus']) && is_array($variation['skus'])) {
                    foreach($variation['skus'] as $sku) {
                        if(
                            isset($sku['myfavCraftSettings']) &&
                            isset($sku['myfavCraftSettings']['activated']) &&
                            $sku['myfavCraftSettings']['activated'] === true &&
                            isset($sku['myfavCraftSettings']['productNumber'])
                        ) {
                            $productNumbersToCheck[] = $sku['myfavCraftSettings']['productNumber'];
                        }
                    }
                }
            }
        }

        $importStatusDto = $this->articleNumberStatusService->checkProductNumberArrayForDuplicates($context, $productNumbersToCheck);

        if($importStatusDto->hasErrors()) {
            return $importStatusDto;
        }

        // Save input data for later processing.
        $myfavVereinArticleId = $this->myfavVereinArticleService->save(
            $context,
            $myfavVereinId,
            $myfavCraftImportArticleId,
            $customProductSettings,
            $overriddenCustomProductSettings,
            $variations
        );

        // Load crafts import article data.
        $myfavCraftImportArticle = $this->myfavCraftImportArticleService->loadById($context, $myfavCraftImportArticleId);
        $craftData = $myfavCraftImportArticle->getCraftData();

        // Parse input data. This could also be done by the values that have been saved in the method above,
        // to reconstruct an import, even if the data is not longer provided by the craft api.
        $swData = $this->craftApiResultParser->getShopwareArticleDataFromCraft($context, $craftData);
        $swData = $this->customDataParser->setCustomArticleData($context, $swData, $myfavCraftImportArticle->getCustomData());
        $swData = $this->customDataParser->setCustomArticleData($context, $swData, $customProductSettings);

        return $this->importVereinArticleService->saveProductAndVariants($context, $myfavVereinArticleId, $myfavCraftImportArticleId, $craftData, $swData, $variations);
    }
}