<?php

declare(strict_types=1);

namespace App\Factory;

use App\DataFixtures\Concern\TimestampBackdateTrait;
use App\Vote\Domain\Model\Vote;

final class VoteFactory
{
    use TimestampBackdateTrait;

    /**
     * @param array<string> $userIds User IDs to vote from
     * @param array<string> $postIds Post IDs to vote on
     * @param array<string> $commentIds Comment IDs to vote on
     * @return Vote[]
     */
    public function createAll(array $userIds, array $postIds, array $commentIds): array
    {
        $votes = [];
        $seen = [];
        $voteCount = 0;
        $maxVotes = 500;

        // Create array of all possible targets
        $targets = [];
        foreach ($postIds as $postId) {
            $targets[] = ['type' => 'post', 'id' => $postId];
        }
        foreach ($commentIds as $commentId) {
            $targets[] = ['type' => 'comment', 'id' => $commentId];
        }

        // Shuffle to randomize voting distribution
        shuffle($targets);
        shuffle($userIds);

        foreach ($targets as $target) {
            foreach ($userIds as $userId) {
                if ($voteCount >= $maxVotes) {
                    break 2;
                }

                // Check uniqueness constraint: (userId, targetType, targetId)
                $key = "{$userId}|{$target['type']}|{$target['id']}";
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                // 80% upvotes, 20% downvotes
                $value = random_int(1, 5) <= 4 ? 1 : -1;
                $createdAt = $this->randomPastDate(180);

                /** @var Vote $vote */
                $vote = $this->buildViaReflection(Vote::class, [
                    'userId' => $userId,
                    'targetType' => $target['type'],
                    'targetId' => $target['id'],
                    'value' => $value,
                    'createdAt' => $createdAt,
                ]);

                $votes[] = $vote;
                $voteCount++;
            }
        }

        return $votes;
    }
}
