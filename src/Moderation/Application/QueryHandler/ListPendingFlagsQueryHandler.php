<?php

declare(strict_types=1);

namespace App\Moderation\Application\QueryHandler;

use App\Moderation\Application\DTO\FlagDTO;
use App\Moderation\Application\DTO\FlagListDTO;
use App\Moderation\Application\Query\ListPendingFlagsQuery;
use App\Moderation\Domain\Repository\FlagRepositoryInterface;
use App\User\Application\DTO\UserInlineDTO;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPendingFlagsQueryHandler
{
    public function __construct(
        private FlagRepositoryInterface $flagRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(ListPendingFlagsQuery $query): FlagListDTO
    {
        $flags = $this->flagRepository->findPendingFlags($query->limit, $query->offset);
        $total = $this->flagRepository->countPendingFlags();

        $reporterIds = array_map(fn($f) => $f->getReporterId(), $flags);
        $resolverIds = array_values(array_filter(array_map(fn($f) => $f->getResolvedById(), $flags)));
        $usersById = $this->userRepository->findByIds(
            array_values(array_unique(array_merge($reporterIds, $resolverIds)))
        );

        $flagDTOs = array_map(
            function ($flag) use ($usersById) {
                $reporter = $usersById[$flag->getReporterId()] ?? null;

                if ($reporter === null) {
                    throw UserNotFoundException::withId($flag->getReporterId());
                }

                $resolvedBy = null;

                if ($flag->getResolvedById() !== null) {
                    $resolver = $usersById[$flag->getResolvedById()] ?? null;

                    if ($resolver === null) {
                        throw UserNotFoundException::withId($flag->getResolvedById());
                    }

                    $resolvedBy = new UserInlineDTO($resolver->getId(), $resolver->getUsername());
                }

                return new FlagDTO(
                    id: $flag->getId(),
                    targetType: $flag->getTargetType(),
                    targetId: $flag->getTargetId(),
                    reporter: new UserInlineDTO($reporter->getId(), $reporter->getUsername()),
                    reason: $flag->getReason(),
                    status: $flag->getStatus(),
                    resolvedBy: $resolvedBy,
                    resolvedAt: $flag->getResolvedAt(),
                    createdAt: $flag->getCreatedAt()
                );
            },
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
