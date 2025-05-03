<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\MyfavCraftImportImage;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                   add(MyfavCraftImportImageEntity $entity)
 * @method void                   set(string $key, MyfavCraftImportImageEntity $entity)
 * @method MyfavCraftImportImageEntity[]    getIterator()
 * @method MyfavCraftImportImageEntity[]    getElements()
 * @method MyfavCraftImportImageEntity|null get(string $key)
 * @method MyfavCraftImportImageEntity|null first()
 * @method MyfavCraftImportImageEntity|null last()
 */
class MyfavCraftImportImageCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return MyfavCraftImportImageEntity::class;
    }
}
