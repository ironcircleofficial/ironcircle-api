<?php

declare(strict_types=1);

namespace App\AI\UI\Http\Transformer;

use App\AI\Application\DTO\AISummaryDTO;
use League\Fractal\TransformerAbstract;

final class AISummaryTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(AISummaryDTO $summary): array
    {
        return [
            'id' => $summary->id,
            'postId' => $summary->postId,
            'summary' => $summary->summary,
            'model' => $summary->model,
            'generatedAt' => $summary->generatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
