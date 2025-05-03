<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\MyfavCraftImportArticle;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                   add(MyfavCraftImportArticleEntity $entity)
 * @method void                   set(string $key, MyfavCraftImportArticleEntity $entity)
 * @method MyfavCraftImportArticleEntity[]    getIterator()
 * @method MyfavCraftImportArticleEntity[]    getElements()
 * @method MyfavCraftImportArticleEntity|null get(string $key)
 * @method MyfavCraftImportArticleEntity|null first()
 * @method MyfavCraftImportArticleEntity|null last()
 */
class MyfavCraftImportArticleCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return MyfavCraftImportArticleEntity::class;
    }
}
