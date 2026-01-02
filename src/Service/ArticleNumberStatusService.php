<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Doctrine\DBAL\Connection;
use Myfav\CraftImport\Dto\ImportStatusDto;
use Myfav\CraftImport\Service\ProductService;
use Shopware\Core\Framework\Context;

class ArticleNumberStatusService
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ImportedArticleService $importedArticleService,
        private readonly ProductService $productService,
    )
    {
    }

    public function checkForDuplicates(Context $context, mixed $mainProductData, mixed $shopwareVariants): ImportStatusDto
    {
        // Get main product by main product number, if it exists.
        $mainProductNumber = $mainProductData['productNumber']->getValue();
        $mainProduct = $this->productService->getProductByProductNumber($context, $mainProductNumber);
        $mainProductId = null;

        if($mainProduct !== null) {
            $mainProductId = $mainProduct->getId();
            $importedArticle = $this->importedArticleService->getEntryByProductNumber($context, $mainProductNumber);

            if($importedArticle === null ||
               $importedArticle->getProductId() !== $mainProductId) {
                $importStatusDto = new ImportStatusDto();
                $importStatusDto->setErrorState(true);
                $importStatusDto->addErrorMessage('Product-Number ' . $mainProductNumber . ' of main product already exists.');
                return $importStatusDto;
            }
        }

        // Query all shopware variant ids for duplicate product numbers.
        $variantProductNumbers = [];

        foreach($shopwareVariants as $variantData) {
            $variantProductNumber = $variantData->getShopwareFieldsForVariant()['productNumber'];

            // If there is a duplicate in the provided variants.
            if(isset($variantProductNumbers[$variantProductNumber])) {
                $importStatusDto = new ImportStatusDto();
                $importStatusDto->setErrorState(true);
                $importStatusDto->addErrorMessage('Your variant data has a dublicate product number: ' . $variantProductNumber);
                return $importStatusDto;
            }

            // If one variant has the same productNumber as the main product.
            if($mainProductNumber == $variantProductNumber) {
                $importStatusDto = new ImportStatusDto();
                $importStatusDto->setErrorState(true);
                $importStatusDto->addErrorMessage('One of your variant productNumbers is the same as the main product: ' . $variantProductNumber);
                return $importStatusDto;
            }

            $variantProductNumbers[$variantProductNumber] = $variantProductNumber;
        }

        // Check, if one of the variant product numbers is assigned to a product.
        // If it is already assigned, make sure, that it is assigned to a mainProduct,
        //    that uses the same mainProductId that our mainProduct has.
        $importStatusDto = $this->checkVariantArticleNumbersStati($context, $mainProductId, $variantProductNumbers);

        return $importStatusDto;
    }

    /**
     * Checks an array of productNumbers for duplicates.
     * Returns importStatusDto object with the duplicates it found.
     * This is a very simple, and just checks, if a product number is already existing in the database.
     */
    public function checkProductNumberArrayForDuplicates($context, $productNumbers) {
        $query = 'SELECT HEX(id) as id, product_number ';
        $query .= 'FROM product WHERE ' . "\n";
        $inputVariables = [];
        $inputVariableCount = 1;

        foreach($productNumbers as $productNumber) {
            if($inputVariableCount > 1) {
                $query .= ' OR ' . "\n";
            }

            $query .= '(product_number = :productNumber' . $inputVariableCount . ')';
            $inputVariables['productNumber' . $inputVariableCount] = $productNumber;
            $inputVariableCount += 1;
        }

        $stmt = $this->connection->executeQuery(
            $query,
            $inputVariables,
        );

        $duplicateProductNumbers = [];

        foreach ($stmt->iterateAssociative() as $row) {
            $duplicateProductNumbers[] = [
                'productId' => $row['id'],
                'productNumber' => $row['product_number']
            ];
        }

        $importStatusDto = new ImportStatusDto();

        if(count($duplicateProductNumbers) == 0) {
            $importStatusDto->setErrorState(false);
        } else {
            $importStatusDto->setErrorState(true);

            $duplicateProductNumbersString = '';

            foreach($duplicateProductNumbers as $duplicateProductNumber) {
                if(strlen($duplicateProductNumbersString) > 0) {
                    $duplicateProductNumbersString .= ', ';
                }

                $duplicateProductNumbersString .= $duplicateProductNumber['productNumber'];
            }

            $importStatusDto->addErrorMessage('Die Artikelnummern der folgenden Varianten sind bereits Artikeln  zugeordnet: ' . $duplicateProductNumbersString);
            $importStatusDto->addData('invalidProductNumbersData', $duplicateProductNumbers);
        }

        return $importStatusDto;
    }

    /**
     * checkVariantArticleNumbersStati
     *
     * @param  Context $context
     * @param  string|null $mainProductId
     * @param  array $variantProductNumbers
     * @return ImportStatusDto
     */
    public function checkVariantArticleNumbersStati(Context $context, ?string $mainProductId, array $variantProductNumbers): ImportStatusDto
    {
        $query = 'SELECT HEX(id) as id, product_number ';
        $query .= 'FROM product WHERE ' . "\n";
        $inputVariables = [];
        $inputVariableCount = 1;

        foreach($variantProductNumbers as $variantProductNumber) {
            if($inputVariableCount > 1) {
                $query .= ' OR ' . "\n";
            }

            if($mainProductId === null) {
                $query .= '(product_number = :productNumber' . $inputVariableCount . ')';
                $inputVariables['productNumber' . $inputVariableCount] = $variantProductNumber;
                $inputVariableCount += 1;
            } else {
                $query .= ' ((product_number = :productNumber' . $inputVariableCount . ' AND parent_id IS NULL) OR';
                $query .= '(product_number = :productNumber'. ($inputVariableCount + 1) . ' and parent_id != UNHEX(:parentId' . ($inputVariableCount + 2) . ')))';
                $inputVariables['productNumber' . $inputVariableCount] = $variantProductNumber;
                $inputVariables['productNumber' . ($inputVariableCount+1)] = $variantProductNumber;
                $inputVariables['parentId' . ($inputVariableCount+2)] = $mainProductId;
                $inputVariableCount += 3;
            }
        }

        $stmt = $this->connection->executeQuery(
            $query,
            $inputVariables,
        );

        $duplicateProductNumbers = [];

        foreach ($stmt->iterateAssociative() as $row) {
            $duplicateProductNumbers = $row['product_number'];
        }

        $importStatusDto = new ImportStatusDto();

        if(count($duplicateProductNumbers) == 0) {
            $importStatusDto->setErrorState(false);
        } else {
            $importStatusDto->setErrorState(true);
            $importStatusDto->addErrorMessage('Die Artikelnummern der folgenden Varianten sind bereits Artikeln  zugeordnet: ' . implode(', ', $duplicateProductNumbers));
        }

        return $importStatusDto;
    }
}