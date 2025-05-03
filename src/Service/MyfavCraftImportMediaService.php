<?php declare(strict_types=1);

namespace Myfav\CraftImport\Service;

use Shopware\Core\Content\Media\Exception\DuplicatedMediaFileNameException;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class MyfavCraftImportMediaService
{
    const TEMP_NAME = 'image-import-from-url'; // Prefix for temporary files, downloaded from URL.
    const MEDIA_DIR = '/public/media/'; // Relative path to Shopware's media directory.
    const MEDIA_FOLDER = 'product'; // Name of the folder in Shopware's media data structure.

    /**
     * ImageImport constructor.
     *
     * @param EntityRepositoryInterface $mediaRepository
     * @param MediaService $mediaService
     * @param FileSaver $fileSaver
     */
    public function __construct (
        private readonly EntityRepository $mediaRepository,
        private readonly MediaService $mediaService,
        private readonly FileSaver $fileSaver
    )
    {
    }

    /**
     * Downloads a file from an URL.
     *
     * @param string $imageUrl
     * @param Context $context
     * @return string|null
     */
    public function addImageToMediaFromURL (string $imageUrl, Context $context)
    {
        throw new \Exception('Not implemented');
        /*
        Need to re-implement
        
        $mediaId = null;

        //process with the cache disabled
        $context->disableCache(function (Context $context) use ($imageUrl, &$mediaId): void {
            //parse the URL
            $filePathParts = explode('/', $imageUrl);
            $fileNameParts = explode('.', array_pop($filePathParts));

            //get the file name and extension
            $fileName = $fileNameParts[0];
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

            if ($fileName && $fileExtension) {
                //copy the file from the URL to the newly created local temporary file
                $filePath = tempnam(sys_get_temp_dir(), self::TEMP_NAME);
                file_put_contents($filePath, file_get_contents($imageUrl));

                //create media record from the image
                $mediaId = $this->createMediaFromFile($filePath, $fileName, $fileExtension, $context);
            }
        });

        return $mediaId;
        */
    }

    /**
     * Method, that returns an ID of a newly created media, based on a local file from the Shopware's media directory
     *
     * @param string $directoryName
     * @param string $fileName
     * @param string $fileExtension
     * @param Context $context
     * @return string|null
     */
    public function addImageToMediaFromFile (
        string $directoryName,
        string $fileName,
        string $fileExtension,
        Context $context,)
    {
        $filepath = $directoryName . '/' . $fileName . '.' . $fileExtension;
        return $this->createMediaFromFile($filepath, $fileName, $fileExtension, $context);
    }

    /**
     * Creates a new media record from a local file and returns its ID
     *
     * @param string $filePath
     * @param string $fileName
     * @param string $fileExtension
     * @param Context $context
     * @return string|null
     */
    private function createMediaFromFile (string $filePath, string $fileName,string $fileExtension, Context $context)
    {
        $mediaId = null;

        //get additional info on the file
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        //create and save new media file to the Shopware's media library
        try {
            $mediaFile = new MediaFile($filePath, $mimeType, $fileExtension, $fileSize);
            $mediaId = $this->mediaService->createMediaInFolder(self::MEDIA_FOLDER, $context, false);
            
            $this->fileSaver->persistFileToMedia(
                $mediaFile,
                $fileName,
                $mediaId,
                $context
            );
        }
        catch (DuplicatedMediaFileNameException $e) {
            echo($e->getMessage());
            $this->mediaCleanup($mediaId, $context);
            $mediaId = null;
        }
        catch (\Exception $e) {
            echo($e->getMessage());
            $this->mediaCleanup($mediaId, $context);
            $mediaId = null;
        }

        return $mediaId;
    }

    /**
     * Deletes media records by id.
     *
     * @param string $mediaId
     * @param Context $context
     * @return null
     */
    private function mediaCleanup (string $mediaId, Context $context)
    {
        $this->mediaRepository->delete([['id' => $mediaId]], $context);
        return null;
    }

    /**
     * findMediaDuplicateFilename
     *
     * @return mixed
     */
    public function findMediaDuplicateFilename($context, $filename, $fileExtension): mixed
    {
        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new EqualsFilter('fileName', $filename),
                new EqualsFilter('fileExtension', $fileExtension),
            ]
        ));

        $media = $this->mediaRepository->search($criteria, $context)->getEntities();

        return $media;
    }
}