<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\MyfavVereinArticle;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                   add(MyfavVereinArticleEntity $entity)
 * @method void                   set(string $key, MyfavVereinArticleEntity $entity)
 * @method MyfavVereinArticleEntity[]    getIterator()
 * @method MyfavVereinArticleEntity[]    getElements()
 * @method MyfavVereinArticleEntity|null get(string $key)
 * @method MyfavVereinArticleEntity|null first()
 * @method MyfavVereinArticleEntity|null last()
 */
class MyfavVereinArticleCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return MyfavVereinArticleEntity::class;
    }
}
