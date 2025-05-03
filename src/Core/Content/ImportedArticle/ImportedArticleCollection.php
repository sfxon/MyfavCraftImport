<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\ImportedArticle;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                   add(ImportedArticleEntity $entity)
 * @method void                   set(string $key, ImportedArticleEntity $entity)
 * @method ImportedArticleEntity[]    getIterator()
 * @method ImportedArticleEntity[]    getElements()
 * @method ImportedArticleEntity|null get(string $key)
 * @method ImportedArticleEntity|null first()
 * @method ImportedArticleEntity|null last()
 */
class ImportedArticleCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ImportedArticleEntity::class;
    }
}
