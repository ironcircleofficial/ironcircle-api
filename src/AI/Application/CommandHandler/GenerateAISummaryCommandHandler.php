<?php

declare(strict_types=1);

namespace App\AI\Application\CommandHandler;

use App\AI\Application\Command\GenerateAISummaryCommand;
use App\AI\Application\Service\SummaryGeneratorInterface;
use App\AI\Domain\Exception\AISummaryNotEnabledException;
use App\AI\Domain\Model\AISummary;
use App\AI\Domain\Repository\AISummaryRepositoryInterface;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GenerateAISummaryCommandHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private AISummaryRepositoryInterface $aiSummaryRepository,
        private SummaryGeneratorInterface $summaryGenerator
    ) {
    }

    public function __invoke(GenerateAISummaryCommand $command): void
    {
        $post = $this->postRepository->findById($command->postId);

        if ($post === null) {
            throw PostNotFoundException::withId($command->postId);
        }

        if (!$post->isAiSummaryEnabled()) {
            throw AISummaryNotEnabledException::forPost($command->postId);
        }

        $existing = $this->aiSummaryRepository->findByPostId($command->postId);

        if ($existing !== null) {
            return;
        }

        $textToSummarize = $post->getTitle() . "\n\n" . $post->getContent();
        $summaryText = $this->summaryGenerator->generateSummary($textToSummarize);

        $summary = new AISummary(
            postId: $command->postId,
            summary: $summaryText,
            model: $this->summaryGenerator->getModelName()
        );

        $this->aiSummaryRepository->save($summary);
    }
}
