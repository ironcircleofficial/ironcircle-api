<?php

declare(strict_types=1);

namespace App\Circle\Application\CommandHandler;

use App\Circle\Application\Command\CreateCircleCommand;
use App\Circle\Domain\Exception\CircleAlreadyExistsException;
use App\Circle\Domain\Model\Circle;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsMessageHandler]
final readonly class CreateCircleCommandHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository,
        private SluggerInterface $slugger
    ) {
    }

    public function __invoke(CreateCircleCommand $command): void
    {
        $slug = $this->slugger->slug($command->name)->lower()->toString();

        if ($this->circleRepository->existsBySlug($slug)) {
            throw CircleAlreadyExistsException::withSlug($slug);
        }

        if ($this->circleRepository->existsByName($command->name)) {
            throw CircleAlreadyExistsException::withName($command->name);
        }

        $circle = new Circle(
            name: $command->name,
            slug: $slug,
            description: $command->description,
            visibility: $command->visibility,
            creatorId: $command->creatorId,
            moderatorIds: [$command->creatorId]
        );

        $this->circleRepository->save($circle);
    }
}
