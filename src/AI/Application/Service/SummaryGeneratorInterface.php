<?php

declare(strict_types=1);

namespace App\AI\Application\Service;

interface SummaryGeneratorInterface
{
    public function generateSummary(string $text): string;

    public function getModelName(): string;
}
