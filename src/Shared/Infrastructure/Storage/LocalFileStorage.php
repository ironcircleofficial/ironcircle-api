<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Storage;

use App\Shared\Application\Service\FileStorageInterface;
use Symfony\Component\Filesystem\Filesystem;

final class LocalFileStorage implements FileStorageInterface
{
    private readonly string $storagePath;
    private readonly Filesystem $filesystem;

    public function __construct(string $projectDir)
    {
        $this->storagePath = $projectDir . '/var/uploads';
        $this->filesystem = new Filesystem();
    }

    public function store(string $sourcePath, string $directory, string $filename): string
    {
        $targetDir = $this->storagePath . '/' . $directory;

        if (!$this->filesystem->exists($targetDir)) {
            $this->filesystem->mkdir($targetDir, 0755);
        }

        $targetPath = $targetDir . '/' . $filename;
        $this->filesystem->copy($sourcePath, $targetPath, true);

        return $filename;
    }

    public function delete(string $storedFilename, string $directory): void
    {
        $filePath = $this->storagePath . '/' . $directory . '/' . $storedFilename;

        if ($this->filesystem->exists($filePath)) {
            $this->filesystem->remove($filePath);
        }
    }

    public function getAbsolutePath(string $storedFilename, string $directory): string
    {
        return $this->storagePath . '/' . $directory . '/' . $storedFilename;
    }
}
