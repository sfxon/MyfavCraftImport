<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class PropertyService
{
    public function __construct(
        private readonly EntityRepository $propertyGroupRepository,
        private readonly EntityRepository $propertyGroupOptionRepository,)
    {
    }

    /**
     * getOptionById
     *
     * @param  Context $context
     * @param  string $propertyGroupOptionId
     * @return PropertyGroupOptionEntity
     */
    public function getOptionById(Context $context, string $propertyGroupOptionId): ?PropertyGroupOptionEntity
    {
        $criteria = new Criteria([$propertyGroupOptionId]);
        $option = $this->propertyGroupOptionRepository->search($criteria, $context)->first();
        return $option;
    }

    /**
     * getOptionByName
     *
     * @param  Context $context
     * @param  string $propertyGroupId
     * @param  string $propertyOptionName
     * @return PropertyGroupOptionEntity
     */
    public function getOptionByName(Context $context, string $propertyGroupId, string $propertyOptionName): ?PropertyGroupOptionEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('groupId', $propertyGroupId));
        $criteria->addFilter(new EqualsFilter('name', $propertyOptionName));
        $option = $this->propertyGroupOptionRepository->search($criteria, $context)->first();

        return $option;
    }

    /**
     * createOption
     *
     * @param  Context $context
     * @param  string $propertyGroupId
     * @param  string $propertyOptionName
     * @param  string|null $colorCode
     * @return string
     */
    public function createOption(Context $context, string $propertyGroupId, string $propertyOptionName, ?string $colorHexCode = null): string
    {
        $context = Context::createDefaultContext();
        $propertyGroupOptionId = Uuid::randomHex();

        $this->propertyGroupOptionRepository->create([
            [
                'id' => $propertyGroupOptionId,
                'name' => $propertyOptionName,
                'groupId' => $propertyGroupId,
                'colorHexCode' => $colorHexCode
            ]
        ], $context);

        return $propertyGroupOptionId;
    }

    /**
     * upsertPropertyGroup
     *
     * @param  Context $context
     * @param  string $propertyGroupName
     * @return string
     */
    public function upsertPropertyGroup(Context $context, string $propertyGroupName): string
    {
        $propertyGroup = $this->getPropertyGroupByName($context, $propertyGroupName);
        
        if($propertyGroup !== null) {
            return $propertyGroup->getId();
        }

        return $this->createPropertyGroup($context, $propertyGroupName);
    }

    /**
     * getPropertyGroupByName
     *
     * @param  Context $context
     * @param  string $propertyGroupName
     * @return PropertyGroupEntity
     */
    public function getPropertyGroupByName(Context $context, string $propertyGroupName): ?PropertyGroupEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $propertyGroupName));
        $option = $this->propertyGroupRepository->search($criteria, $context)->first();
        return $option;
    }

    /**
     * createPropertyGroup
     *
     * @param  Context $context
     * @param  string $propertyGroupName
     * @return string
     */
    public function createPropertyGroup(Context $context, string $propertyGroupName): string
    {
        $context = Context::createDefaultContext();
        $propertyGroupId = Uuid::randomHex();

        $this->propertyGroupRepository->create([
            [
                'id' => $propertyGroupId,
                'name' => $propertyGroupName,
            ]
        ], $context);

        return $propertyGroupId;
    }
}