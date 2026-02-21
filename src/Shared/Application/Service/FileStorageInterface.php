<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

interface FileStorageInterface
{
    public function store(string $sourcePath, string $directory, string $filename): string;

    public function delete(string $storedFilename, string $directory): void;

    public function getAbsolutePath(string $storedFilename, string $directory): string;
}
