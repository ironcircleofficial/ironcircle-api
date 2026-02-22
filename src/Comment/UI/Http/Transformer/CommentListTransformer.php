<?php

declare(strict_types=1);

namespace App\Comment\UI\Http\Transformer;

use App\Comment\Application\DTO\CommentListDTO;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class CommentListTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['comments'];

    /**
     * @return array<string, mixed>
     */
    public function transform(CommentListDTO $commentList): array
    {
        return [
            'pagination' => [
                'total' => $commentList->total,
                'limit' => $commentList->limit,
                'offset' => $commentList->offset,
            ],
        ];
    }

    public function includeComments(CommentListDTO $commentList): Collection
    {
        return $this->collection($commentList->comments, new CommentTransformer());
    }
}
