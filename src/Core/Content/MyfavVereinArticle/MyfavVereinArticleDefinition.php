<?php declare(strict_types=1);

namespace Myfav\CraftImport\Core\Content\MyfavVereinArticle;

use Myfav\CraftImport\Core\Content\MyfavCraftImportArticle\MyfavCraftImportArticleDefinition;
use Myfav\CraftImport\Core\Content\MyfavVerein\MyfavVereinDefinition;
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

class MyfavVereinArticleDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'myfav_verein_article';

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
        return MyfavVereinArticleEntity::class;
    }

    /**
     * getCollectionClass
     *
     * @return string
     */
    public function getCollectionClass(): string
    {
        return MyfavVereinArticleCollection::class;
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
            (new FkField('myfav_verein_id', 'myfavVereinId', MyfavVereinDefinition::class))->addFlags(new Required()),
            (new FkField('myfav_craft_import_article_id', 'myfavCraftImportArticleId', MyfavCraftImportArticleDefinition::class))->addFlags(new Required()),
            (new JsonField('custom_product_settings', 'customProductSettings')),
            (new JsonField('overridden_custom_product_settings', 'overriddenCustomProductSettings')),
            (new JsonField('variations', 'variations')),

            // Associations
            new ManyToOneAssociationField('myfavVerein', 'myfav_verein_id', MyfavVereinDefinition::class, 'id', false),
            new ManyToOneAssociationField('myfavCraftImportArticle', 'myfav_craft_import_article_id', MyfavCraftImportArticleDefinition::class, 'id', false),
        ]);
    }
}
