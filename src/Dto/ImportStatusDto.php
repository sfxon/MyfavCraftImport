<?php declare(strict_types=1);

namespace Myfav\CraftImport\Dto;

class ImportStatusDto {
    private bool $hasError = false;
    private array $errorMessages = [];

    // hasError
    public function hasErrors(): bool
    {
        return $this->hasError;
    }

    public function setErrorState(bool $hasError): void
    {
        $this->hasError = $hasError;
    }

    // errorMessage
    public function addErrorMessage(string $errorMessage) {
        $this->errorMessages[] = $errorMessage;
    }

    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    public function setErrorMessages(array $errorMessages): void
    {
        $this->errorMessages = $errorMessages;
    }
}