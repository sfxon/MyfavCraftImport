<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;

class MyfavVereinArticleService
{
    public function __construct(
        private readonly EntityRepository $myfavVereinArticleRepository,
        )
    {
    }

    /**
     * save
     *
     * @param  Context $context
     * @param  string $myfavVereinId
     * @param  string $myfavCraftImportArticleId
     * @param  mixed $customProductSettings
     * @param  mixed $overriddenCustomProductSettings
     * @param  mixed $variations
     * @return string
     */
    public function save(
        Context $context,
        string $myfavVereinId,
        mixed $myfavCraftImportArticleId,
        mixed $customProductSettings,
        mixed $overriddenCustomProductSettings,
        mixed $variations): string
    {
        $id = Uuid::randomHex();

        $data = [
            'id' => $id,
            'myfavVereinId' => $myfavVereinId,
            'myfavCraftImportArticleId' => $myfavCraftImportArticleId,
            'customProductSettings' => $customProductSettings,
            'overriddenCustomProductSettings' => $overriddenCustomProductSettings,
            'variations' => $variations
        ];

        $this->myfavVereinArticleRepository->upsert([$data], $context);

        return $id;
    }
}