<?php

declare(strict_types=1);

namespace App\Moderation\Application\QueryHandler;

use App\Moderation\Application\DTO\FlagDTO;
use App\Moderation\Application\Query\GetFlagByIdQuery;
use App\Moderation\Domain\Exception\FlagNotFoundException;
use App\Moderation\Domain\Repository\FlagRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetFlagByIdQueryHandler
{
    public function __construct(
        private FlagRepositoryInterface $flagRepository
    ) {
    }

    public function __invoke(GetFlagByIdQuery $query): FlagDTO
    {
        $flag = $this->flagRepository->findById($query->flagId);

        if ($flag === null) {
            throw FlagNotFoundException::withId($query->flagId);
        }

        return new FlagDTO(
            id: $flag->getId(),
            targetType: $flag->getTargetType(),
            targetId: $flag->getTargetId(),
            reporterId: $flag->getReporterId(),
            reason: $flag->getReason(),
            status: $flag->getStatus(),
            resolvedById: $flag->getResolvedById(),
            resolvedAt: $flag->getResolvedAt(),
            createdAt: $flag->getCreatedAt()
        );
    }
}
