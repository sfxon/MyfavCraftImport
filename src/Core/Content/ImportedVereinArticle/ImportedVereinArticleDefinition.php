<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\ImportedVereinArticle;

use Myfav\CraftImport\Core\Content\MyfavVereinArticle\MyfavVereinArticleDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ImportedVereinArticleDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'imported_verein_article';

    /**
     * getEntityName
     *
     * @return string
     */
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    /**
     * getEntityClass
     *
     * @return string
     */
    public function getEntityClass(): string
    {
        return ImportedVereinArticleEntity::class;
    }

    /**
     * getCollectionClass
     *
     * @return string
     */
    public function getCollectionClass(): string
    {
        return ImportedVereinArticleCollection::class;
    }

    /**
     * defineFields
     *
     * @return FieldCollection
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey(), new ApiAware()),
            (new FkField('myfav_verein_article_id', 'myfavVereinArticleId', MyfavVereinArticleDefinition::class)),
            (new FkField('product_id', 'productId', ProductDefinition::class)),
            (new FkField('parent_product_id', 'parentProductId', ProductDefinition::class)),

            (new OneToManyAssociationField('myfavVereinArticle', MyfavVereinArticleDefinition::class, 'id', 'myfav_verein_article_id')),
            (new OneToManyAssociationField('product', ProductDefinition::class, 'id', 'product_id', ))->addFlags(new ApiAware()),
            (new OneToManyAssociationField('parentProduct', ProductDefinition::class, 'id', 'parent_product_id', ))->addFlags(new ApiAware()),
        ]);
    }
}
