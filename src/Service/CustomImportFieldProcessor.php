<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Shopware\Core\Framework\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomImportFieldProcessor {
    /**
     * updateField
     *
     * @return void
     */
    public static function updateField(
        Context $context,
        array &$fields, // Fields that have already been populated with import data from the original data source.
        string $shopwareFieldName, // Fieldname, that should be updated.
        ContainerInterface $container, // Symfony Container for fabric processing.
        mixed $customProductSettings, // Array with new data, that may override existing data.
        ?array $indexPathForUpdateFlag, // Index-Path of the field in customProductSettings, that defines if the data should be updated.
        ?array $indexPathForValue, // Index-Path of the field in customProductSettings, that is holding the new value.
        string $processorServiceName):void // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
    {

        // Get field from the array, that holds the originally processed input data.
        $field = null;

        if(isset($fields[$shopwareFieldName])) {
            $field = $fields[$shopwareFieldName];
        }

        // If the field is not set, create a new and empty field.
        if($field === null) {
            $fields[$shopwareFieldName] = (new ImportField(null, ''));
            $field = $fields[$shopwareFieldName];
        }

        if($indexPathForUpdateFlag !== null) {
            // Process data for the field, if it should be processed.
            $shouldUpdate = self::getArrayDataByIndexPath($customProductSettings, $indexPathForUpdateFlag);

            if(!$shouldUpdate) {
                return;
            }
        }

        $field->updateValueByProcessor($context, $container, $customProductSettings, $indexPathForValue, $processorServiceName);
    }

    /**
     * mergeUpdateArrayField
     *
     * @return void
     */
    public static function mergeUpdateArrayField(
        Context $context,
        array &$fields, // Fields that have already been populated with import data from the original data source.
        string $shopwareFieldName, // Fieldname, that should be updated.
        ContainerInterface $container, // Symfony Container for fabric processing.
        mixed $customProductSettings, // Array with new data, that may override existing data.
        ?array $indexPathForUpdateFlag, // Index-Path of the field in customProductSettings, that defines if the data should be updated.
        ?array $indexPathForValue, // Index-Path of the field in customProductSettings, that is holding the new value.
        string $processorServiceName):void // A service, that is used to process/extend the data, for example it could load id's for text values. Can also do complex processing.
    {

        // Get field from the array, that holds the originally processed input data.
        $field = null;

        if(isset($fields[$shopwareFieldName])) {
            $field = $fields[$shopwareFieldName];
        }

        // If the field is not set, create a new and empty field.
        if($field === null) {
            $fields[$shopwareFieldName] = (new ImportField(null, ''));
            $field = $fields[$shopwareFieldName];
        }

        if($indexPathForUpdateFlag !== null) {
            // Process data for the field, if it should be processed.
            $shouldUpdate = self::getArrayDataByIndexPath($customProductSettings, $indexPathForUpdateFlag);

            if(!$shouldUpdate) {
                return;
            }
        }

        $field->mergeArrayValueByProcessor($context, $container, $customProductSettings, $indexPathForValue, $processorServiceName);
    }

    /**
     * getArrayDataByIndexPath
     *
     * @param  mixed $array
     * @param  mixed $indexPath
     * @return mixed
     */
    public static function getArrayDataByIndexPath($array, $indexPath): mixed
    {
        // Daten auslesen.
        $data = $array;

        foreach ($indexPath as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                $data = null;
                break;
            }
        }

        return $data;
    }
}