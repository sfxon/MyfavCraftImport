<?php declare(strict_types=1);

namespace Myfav\CraftImport\Dto;

class ImportStatusDto {
    private array $data = [];
    private bool $hasError = false;
    private array $errorMessages = [];

    // data
    public function addData(string $index, mixed $data) {
        $this->data[$index] = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

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