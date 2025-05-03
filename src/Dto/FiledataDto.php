<?php declare(strict_types=1);

namespace Myfav\CraftImport\Dto;

class FiledataDto {
    private ?string $filepath = null;
    private ?string $contentHash = null;
    private int $position = 0;
    private ?string $mediaId = null;
    private ?string $productMediaId = null;

    // filepath
    public function getFilepath(): ?string
    {
        return $this->filepath;
    }

    public function setFilepath(string $filepath): void
    {
        $this->filepath = $filepath;
    }

    public function getFilenameWithoutExtension(): ?string
    {
        if($this->filepath === null) {
            return null;
        }

        $fileExtension = $this->getFilenameExtension();
        $retFilename = rtrim(basename($this->filepath), '.' . $fileExtension);

        return $retFilename;
    }

    public function getFilename(): ?string
    {
        if($this->filepath === null) {
            return null;
        }

        $retFilename = basename($this->filepath);

        return $retFilename;
    }

    public function getFilenameExtension(): ?string
    {
        if($this->filepath === null) {
            return null;
        }

        $fileExtension = pathinfo(basename($this->filepath), PATHINFO_EXTENSION);

        return $fileExtension;
    }

    // contentHash
    public function getContentHash(): ?string
    {
        return $this->contentHash;
    }

    public function setContentHash(string $contentHash): void
    {
        $this->contentHash = $contentHash;
    }

    // position
    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    // mediaId
    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    // productMediaId
    public function getProductMediaId(): ?string
    {
        return $this->productMediaId;
    }

    public function setProductMediaId(string $productMediaId): void
    {
        $this->productMediaId = $productMediaId;
    }
}