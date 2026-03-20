<?php

declare(strict_types=1);

namespace App\Moderation\Application\QueryHandler;

use App\Moderation\Application\DTO\FlagDTO;
use App\Moderation\Application\Query\GetFlagByIdQuery;
use App\Moderation\Domain\Exception\FlagNotFoundException;
use App\Moderation\Domain\Repository\FlagRepositoryInterface;
use App\User\Application\DTO\UserInlineDTO;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetFlagByIdQueryHandler
{
    public function __construct(
        private FlagRepositoryInterface $flagRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(GetFlagByIdQuery $query): FlagDTO
    {
        $flag = $this->flagRepository->findById($query->flagId);

        if ($flag === null) {
            throw FlagNotFoundException::withId($query->flagId);
        }

        $reporter = $this->userRepository->findById($flag->getReporterId());

        if ($reporter === null) {
            throw UserNotFoundException::withId($flag->getReporterId());
        }

        $resolvedBy = null;

        if ($flag->getResolvedById() !== null) {
            $resolver = $this->userRepository->findById($flag->getResolvedById());

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
    }
}
