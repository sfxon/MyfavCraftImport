<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Shopware\Core\Framework\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportField
{
    private mixed $additionalData = null;
    private ?array $fieldDataIndex;
    private mixed $defaultValue;
    private $craftImportProcessorClassName;
    private $customDataProcessorClassName;
    private mixed $value = null;

    public function __construct(
        ?array $fieldDataIndex,
        string $craftImportProcessorClassName,
        mixed $additionalData = null,
        mixed $defaultValue = null)
    {
        $this->fieldDataIndex = $fieldDataIndex;
        $this->craftImportProcessorClassName = $craftImportProcessorClassName;
        $this->additionalData = $additionalData;
        $this->defaultValue = $defaultValue;
    }

    /**
     * getValue
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * processCraftImportService
     *
     * @return void
     */
    public function processCraftImportService(Context $context, ContainerInterface $container, mixed $importData): void
    {
        $craftImportProcessorInstance = $container->get('Myfav\\CraftImport\\ImportFieldService\\' . $this->craftImportProcessorClassName);
        $this->value = $craftImportProcessorInstance->process($context, $importData, $this->fieldDataIndex, $this->additionalData, $this->defaultValue);
    }

    /**
     * updateValueByProcessor
     *
     * @param  Context $context
     * @param  ContainerInterface $container
     * @param  mixed $customProductSettings
     * @param  array|null $indexPathForValue
     * @param  string $customDataProcessorClassName
     * @param  bool $newValueCanBeNull
     * @return void
     */
    public function updateValueByProcessor(
        Context $context,
        ContainerInterface $container,
        mixed $customProductSettings,
        ?array $indexPathForValue,
        string $customDataProcessorClassName,
        bool $newValueCanBeNull = false): void
    {
        $newValue = $this->processCustomDataProcessor(
            $context,
            $container,
            $customProductSettings,
            $indexPathForValue,
            $customDataProcessorClassName
        );

        if($newValue === null && !$newValueCanBeNull) {
            return;
        }

        $this->value = $newValue;
    }

    /**
     * mergeArrayValueByProcessor
     *
     * @param  Context $context
     * @param  ContainerInterface $container
     * @param  mixed $customProductSettings
     * @param  array|null $indexPathForValue
     * @param  string $customDataProcessorClassName
     * @param  bool $newValueCanBeNull
     * @return void
     */
    public function mergeArrayValueByProcessor(
        Context $context,
        ContainerInterface $container,
        mixed $customProductSettings,
        ?array $indexPathForValue,
        string $customDataProcessorClassName,
        $newValueCanBeNull = false): void
    {
        $newValue = $this->processCustomDataProcessor(
            $context,
            $container,
            $customProductSettings,
            $indexPathForValue,
            $customDataProcessorClassName
        );

        if($newValue === null && !$newValueCanBeNull) {
            return;
        }

        if(!is_array($newValue)) {
            return;
        }

        if(count($newValue) == 0) {
            return;
        }

        if(!is_array($this->value)) {
            $this->value = [];
        }

        foreach($newValue as $index => $value) {
            $this->value[$index] = $value;
        }
    }

    /**
     * processCustomDataProcessor
     *
     * @param  Context $context
     * @param  ContainerInterface $container
     * @param  mixed $customProductSettings
     * @param  array|null $indexPathForValue
     * @param  string $customDataProcessorClassName
     * @return mixed
     */
    private function processCustomDataProcessor(
        Context $context,
        ContainerInterface $container,
        mixed $customProductSettings,
        ?array $indexPathForValue,
        string $customDataProcessorClassName,): mixed
    {
        $this->customDataProcessorClassName = $customDataProcessorClassName;
        $customDataProcessorInstance = $container->get(
            'Myfav\\CraftImport\\ImportFieldService\\' . $this->customDataProcessorClassName
        );
        $newValue = $customDataProcessorInstance->process($context, $customProductSettings, $indexPathForValue);

        return $newValue;
    }
}