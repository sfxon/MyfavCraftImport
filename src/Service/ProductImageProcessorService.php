<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use GuzzleHttp\Client;
use Myfav\CraftImport\Service\ProductService;
use Myfav\CraftImport\Service\MyfavCraftImportMediaService;
use Myfav\CraftImport\Dto\FiledataDto;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class ProductImageProcessorService {
    const TEMP_NAME = 'image-import-from-url'; // Prefix for temporary files, downloaded from URL.

    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly ProductService $productService,
        private readonly MyfavCraftImportMediaService $myfavCraftImportMediaService,
        private readonly EntityRepository $myfavCraftImportImageRepository,
        private readonly EntityRepository $productMediaRepository,
        private readonly EntityRepository $mediaRepository)
    {
    }

    /**
     * process
     *
     * @param  mixed $context
     * @param  mixed $product
     * @param  mixed $data
     * @return void
     */
    public function process(Context $context, string $productId, array $imageUrls)
    {
        $product = $this->productService->getById($context, $productId);

        if(null === $product) {
            return false;
        }

        $cleanImageUrls = [];

        foreach($imageUrls as $imageUrl) {
            $imageUrl = urldecode($imageUrl); // Replace %20 for example.
            $cleanImageUrls[] = $imageUrl;
        }

        $this->importImages($context, $product, $cleanImageUrls);
    }

    /**
     * importImages
     *
     * @param  Context $context
     * @param  mixed $product
     * @param  mixed $files
     * @return void
     */
    private function importImages(Context $context, $product, $files): void
    {
        // Prepare filedata structures
        $filedata = $this->prepareFiledataStructures($context, $files);

        // Temporarly download images.
        $filedata = $this->temporarlyDownloadImages($context, $product, $filedata);

        // Prepare filedata structures
        $files = [];

        foreach($filedata as $tmp) {
            $files[] = $tmp->getFilepath();
        }

        $filedata = $this->prepareFiledataStructures($context, $files);

        // Find images that are already assigend to this product.
        $filedata = $this->findAlreadyAssignedImages($context, $product, $filedata);

        // Check image names and rename the images that should be imported, if they are not already in the shop,
        // because we can assign names only once.
        $filedata = $this->renameImagesIfNecessary($context, $filedata);

        // Upload new media.
        $filedata = $this->uploadNewMedia($context, $product, $filedata);

        // Assign images to product, if not already assigned.
        $filedata = $this->addImagesToProduct($context, $product, $filedata);

        // Update sortings.
        $this->updateProductImagesSortings($context, $product, $filedata);

        // Set first image as cover image.
        $this->setCoverImage($context, $product, $filedata);

        $this->saveImageHashes($context, $filedata);
    }

    /**
     * temporarlyDownloadImages
     */
    private function temporarlyDownloadImages($context, $product, $filedata)
    {
        foreach($filedata as $index => $file) {
            $tmpDirectory = sys_get_temp_dir() . '/' . self::TEMP_NAME . '/';
            $tmpFilePath =  $tmpDirectory . basename($file->getFilepath());

            if(!is_dir($tmpDirectory)) {
                mkdir($tmpDirectory);
            }

            $imageData = '';

            if(strpos($file->getFilepath(), 'http') === 0) {
                $client = new Client();
                $response = $client->get($file->getFilepath());
                $imageData = $response->getBody()->getContents();
            } else {
                $imageData = file_get_contents($file->getFilepath());
            }

            file_put_contents($tmpFilePath, $imageData);

            $filedata[$index]->setFilepath($tmpFilePath);
        }

        return $filedata;
    }

    /**
     * prepareFiledataStructures
     *
     * @param  mixed $files
     * @return array
     */
    private function prepareFiledataStructures($context, $files): array
    {
        $retval = [];

        foreach($files as $index => $file) {
            $entry = new FiledataDto();
            $entry->setFilepath($file);
            $entry->setPosition($index);

            // Get content hash of file contents.
            $contentHash = $this->getFileContentHash($file);
            $entry->setContentHash($contentHash);

            // Try to load the mediaEntry from the database.
            $myfavCraftImportImage = $this->getImageByContentHash($context, $contentHash);

            if(null !== $myfavCraftImportImage) {
                $mediaId = $myfavCraftImportImage->getMediaId();
                $entry->setMediaId($mediaId);
            }

            $retval[$index] = $entry;
        }

        return $retval;
    }

    /**
     * getFileContentHash
     *
     * @param  mixed $filepath
     * @return string
     */
    private function getFileContentHash($filepath): ?string
    {
        $imageData = '';

        if(strpos($filepath, 'http') === 0) {
            $client = new Client();
            $response = $client->get($filepath);
            $imageData = $response->getBody()->getContents();
        } else {
            $imageData = file_get_contents($filepath);
        }

        $hash = hash('sha256', $imageData);
        return $hash;
    }

    /**
     * getImageByContentHash
     *
     * @param  mixed $context
     * @param  mixed $contentHash
     * @return mixed
     */
    private function getImageByContentHash($context, $contentHash): mixed
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('contentHash', $contentHash));
        $criteria->setLimit(1);

        $myfavCraftImportImage = $this->myfavCraftImportImageRepository->search($criteria, $context)->first();

        return $myfavCraftImportImage;
    }

    /**
     * findAlreadyAssignedImages
     *
     * @param  mixed $context
     * @param  mixed $product
     * @param  mixed $filedata
     * @return array
     */
    private function findAlreadyAssignedImages($context, $product, $filedata): array
    {
        $productMedias = $product->getMedia();

        if($productMedias === null) {
            return $filedata;
        }

        foreach($filedata as $index => $entry) {
            foreach($productMedias as $productMedia) {
                $mediaPath = $productMedia->getMedia()->getPath();
                $mediaContentHash = hash_file('sha256', $mediaPath);

                if($mediaContentHash === $entry->getContentHash()) {
                    $filedata[$index]->setMediaId($productMedia->getMedia()->getId());
                    $filedata[$index]->setProductMediaId($productMedia->getId());
                }
            }
        }

        return $filedata;
    }

    /**
     * uploadNewMedia
     *
     * @param  mixed $context
     * @param  mixed $product
     * @param  mixed $filedata
     * @return void
     */
    private function uploadNewMedia($context, $product, $filedata): array
    {
        foreach($filedata as $index => $entry) {
            if($entry->getMediaId() === null) {
                $mediaId = $this->myfavCraftImportMediaService->addImageToMediaFromFile(
                    dirname($entry->getFilepath()),
                    $entry->getFilenameWithoutExtension(),
                    $entry->getFilenameExtension(),
                    $context
                );


                if($mediaId !== null) {
                    $filedata[$index]->setMediaId($mediaId);
                }
            }
        }

        return $filedata;
    }

    /**
     * addImagesToProduct
     *
     * @param  mixed $context
     * @param  mixed $product
     * @param  mixed $filedata
     * @return array
     */
    private function addImagesToProduct($context, $product, $filedata): array
    {
        foreach($filedata as $entry) {
            if($entry->getProductMediaId() === null) {
                $productMediaId = Uuid::randomHex();

                $data = [
                    'id' => $productMediaId,
                    'productId' => $product->getId(),
                    'mediaId' => $entry->getMediaId(),
                    'position' => $entry->getPosition(),
                ];
                
                $this->productMediaRepository->create([$data], $context);

                $entry->setProductMediaId($productMediaId);
            }
        }

        return $filedata;
    }

    /**
     * updateProductImagesSortings
     *
     * @param  mixed $context
     * @param  mixed $product
     * @param  mixed $filedata
     * @return void
     */
    private function updateProductImagesSortings($context, $product, $filedata): void
    {
        foreach($filedata as $entry) {
            if($entry->getProductMediaId() !== null) {
                $data = [
                    'id' => $entry->getProductMediaId(),
                    'position' => $entry->getPosition(),
                ];

                $this->productMediaRepository->update([$data], $context);
            }
        }
    }

    /**
     * renameImagesIfNecessary
     *
     * @param  mixed $filedata
     * @return array
     */
    private function renameImagesIfNecessary($context, $filedata): array
    {
        foreach($filedata as $index => $entry) {
            if($entry->getProductMediaId() === null) {
                $existingMedia = $this->myfavCraftImportMediaService->findMediaDuplicateFilename(
                    $context,
                    $entry->getFilenameWithoutExtension(),
                    $entry->getFilenameExtension()
                )->first();

                if($existingMedia) {
                    // Get file hash.
                    $mediaPath = $existingMedia->getPath();
                    $mediaContentHash = hash_file('sha256', $mediaPath);

                    if($mediaContentHash === $entry->getContentHash()) {
                        $filedata[$index]->setMediaId($existingMedia->getId());
                    }
                }
            }
        }

        return $filedata;
    }

    /**
     * setCoverImage
     *
     * @param  mixed $context
     * @param  mixed $product
     * @param  mixed $filedata
     * @return void
     */
    private function setCoverImage($context, $product, $filedata): void
    {
        if(isset($filedata[0])) {
            if(($filedata[0]->getProductMediaId() !== null)) {
                $data = [
                    'id' => $product->getId(),
                    'coverId' => $filedata[0]->getProductMediaId()
                ];

                $this->productRepository->update([$data], $context);
            }
        }
    }

    /**
     * saveImageHashes
     */
    private function saveImageHashes($context, $filedata) {
        foreach($filedata as $file) {
            // Check, if the entry already exists.
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('mediaId', $file->getMediaId()));
            $criteria->addFilter(new EqualsFilter('contentHash', $file->getContentHash()));
            $myfavCraftImportImage = $this->myfavCraftImportImageRepository->search($criteria, $context)->first();

            // Only insert the entry, if it does not already exist.
            if(null === $myfavCraftImportImage) {
                $data = [
                    'mediaId' => $file->getMediaId(),
                    'contentHash' => $file->getContentHash(),
                ];

                $this->myfavCraftImportImageRepository->create(
                    [
                        $data
                    ],
                    $context
                );
            }
        }
    }
}