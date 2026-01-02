<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\ImportedVereinArticle;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                   add(ImportedVereinArticleEntity $entity)
 * @method void                   set(string $key, ImportedVereinArticleEntity $entity)
 * @method ImportedVereinArticleEntity[]    getIterator()
 * @method ImportedVereinArticleEntity[]    getElements()
 * @method ImportedVereinArticleEntity|null get(string $key)
 * @method ImportedVereinArticleEntity|null first()
 * @method ImportedVereinArticleEntity|null last()
 */
class ImportedVereinArticleCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ImportedVereinArticleEntity::class;
    }
}
