<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\MyfavVerein;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                   add(MyfavVereinEntity $entity)
 * @method void                   set(string $key, MyfavVereinEntity $entity)
 * @method MyfavVereinEntity[]    getIterator()
 * @method MyfavVereinEntity[]    getElements()
 * @method MyfavVereinEntity|null get(string $key)
 * @method MyfavVereinEntity|null first()
 * @method MyfavVereinEntity|null last()
 */
class MyfavVereinCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return MyfavVereinEntity::class;
    }
}
