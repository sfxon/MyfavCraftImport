<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Myfav\CraftImport\Core\Content\MyfavCraftImportArticle\MyfavCraftImportArticleEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class MyfavCraftImportArticleService
{
    public function __construct(
        private readonly EntityRepository $myfavCraftImportArticleRepository,
        )
    {
    }

    /**
     * getEntryByProductNumber
     *
     * @param  Context $context
     * @param  string $productNumber
     * @return null|MyfavCraftImportArticleEntity
     */
    public function getEntryByProductNumber(Context $context, string $productNumber): ?MyfavCraftImportArticleEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addFilter(new EqualsFilter('product.productNumber', $productNumber));
        return $this->myfavCraftImportArticleRepository->search($criteria, $context)->first();
    }

    /**
     * getEntryByCraftProductNumber
     *
     * @param  Context $context
     * @param  string $craftProductNumber
     * @return null|MyfavCraftImportArticleEntity
     */
    public function getEntryByCraftProductNumber(Context $context, string $craftProductNumber): ?MyfavCraftImportArticleEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('craftProductNumber', $craftProductNumber));
        return $this->myfavCraftImportArticleRepository->search($criteria, $context)->first();
    }

    /**
     * saveCustomData
     *
     * @param  Context $context
     * @param  string $craftProductNumber
     * @param  mixed $craftData
     * @param  mixed $customData
     * @return string
     */
    public function saveCustomData(Context $context, string $craftProductNumber, mixed $craftData, mixed $customData): string
    {
        $myfavCraftImportArticle = $this->getEntryByCraftProductNumber($context, $craftProductNumber);

        $id = Uuid::randomHex();

        if(null !== $myfavCraftImportArticle) {
            $id = $myfavCraftImportArticle->getId();
        }

        $data = [
            'id' => $id,
            'craftProductNumber' => $craftProductNumber,
            'craftData' => $craftData,
            'customData' => $customData
        ];

        $this->myfavCraftImportArticleRepository->upsert([$data], $context);

        return $id;
    }
}