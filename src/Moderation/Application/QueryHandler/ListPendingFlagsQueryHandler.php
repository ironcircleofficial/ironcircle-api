<?php

declare(strict_types=1);

namespace App\Moderation\Application\QueryHandler;

use App\Moderation\Application\DTO\FlagDTO;
use App\Moderation\Application\DTO\FlagListDTO;
use App\Moderation\Application\Query\ListPendingFlagsQuery;
use App\Moderation\Domain\Repository\FlagRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPendingFlagsQueryHandler
{
    public function __construct(
        private FlagRepositoryInterface $flagRepository
    ) {
    }

    public function __invoke(ListPendingFlagsQuery $query): FlagListDTO
    {
        $flags = $this->flagRepository->findPendingFlags($query->limit, $query->offset);
        $total = $this->flagRepository->countPendingFlags();

        $flagDTOs = array_map(
            fn($flag) => new FlagDTO(
                id: $flag->getId(),
                targetType: $flag->getTargetType(),
                targetId: $flag->getTargetId(),
                reporterId: $flag->getReporterId(),
                reason: $flag->getReason(),
                status: $flag->getStatus(),
                resolvedById: $flag->getResolvedById(),
                resolvedAt: $flag->getResolvedAt(),
                createdAt: $flag->getCreatedAt()
            ),
            $flags
        );

        return new FlagListDTO(
            flags: $flagDTOs,
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
