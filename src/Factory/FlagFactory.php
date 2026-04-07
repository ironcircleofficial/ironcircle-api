<?php

declare(strict_types=1);

namespace App\Factory;

use App\DataFixtures\Concern\TimestampBackdateTrait;
use App\Moderation\Domain\Model\Flag;

final class FlagFactory
{
    use TimestampBackdateTrait;

    /** @var array<string> */
    private const REASONS = [
        'Spam or promotional content',
        'Harassment or bullying',
        'Offensive language',
        'Misinformation',
        'Low effort content',
        'Off-topic discussion',
        'Self-promotion without value',
        'Duplicate post',
        'Privacy concern',
        'Copyright violation',
        'Threatening behavior',
        'Inappropriate content',
        'Scam or fraud',
        'Excessive caps lock',
        'Repeated violations',
    ];

    /**
     * @param array<string> $postIds Post IDs to flag
     * @param array<string> $commentIds Comment IDs to flag
     * @param array<string> $reporterIds User IDs to report from
     * @param array<string> $moderatorIds Moderator IDs for resolution
     * @return Flag[]
     */
    public function createAll(array $postIds, array $commentIds, array $reporterIds, array $moderatorIds): array
    {
        $flags = [];

        // 10 pending flags
        for ($i = 0; $i < 10; $i++) {
            $isPost = random_int(0, 1) === 0;
            $targetId = $isPost ? $postIds[array_rand($postIds)] : $commentIds[array_rand($commentIds)];
            $reporterId = $reporterIds[array_rand($reporterIds)];
            $reason = self::REASONS[array_rand(self::REASONS)];
            $createdAt = $this->randomPastDate(180);

            /** @var Flag $flag */
            $flag = $this->buildViaReflection(Flag::class, [
                'targetType' => $isPost ? 'post' : 'comment',
                'targetId' => $targetId,
                'reporterId' => $reporterId,
                'reason' => $reason,
                'status' => 'pending',
                'resolvedById' => null,
                'resolvedAt' => null,
                'createdAt' => $createdAt,
            ]);

            $flags[] = $flag;
        }

        // 10 approved flags
        for ($i = 0; $i < 10; $i++) {
            $isPost = random_int(0, 1) === 0;
            $targetId = $isPost ? $postIds[array_rand($postIds)] : $commentIds[array_rand($commentIds)];
            $reporterId = $reporterIds[array_rand($reporterIds)];
            $moderatorId = $moderatorIds[array_rand($moderatorIds)];
            $reason = self::REASONS[array_rand(self::REASONS)];
            $createdAt = $this->randomPastDate(180);
            $resolvedAt = $createdAt->modify('+' . random_int(1, 72) . ' hours');

            /** @var Flag $flag */
            $flag = $this->buildViaReflection(Flag::class, [
                'targetType' => $isPost ? 'post' : 'comment',
                'targetId' => $targetId,
                'reporterId' => $reporterId,
                'reason' => $reason,
                'status' => 'approved',
                'resolvedById' => $moderatorId,
                'resolvedAt' => $resolvedAt,
                'createdAt' => $createdAt,
            ]);

            $flags[] = $flag;
        }

        // 10 rejected flags
        for ($i = 0; $i < 10; $i++) {
            $isPost = random_int(0, 1) === 0;
            $targetId = $isPost ? $postIds[array_rand($postIds)] : $commentIds[array_rand($commentIds)];
            $reporterId = $reporterIds[array_rand($reporterIds)];
            $moderatorId = $moderatorIds[array_rand($moderatorIds)];
            $reason = self::REASONS[array_rand(self::REASONS)];
            $createdAt = $this->randomPastDate(180);
            $resolvedAt = $createdAt->modify('+' . random_int(1, 72) . ' hours');

            /** @var Flag $flag */
            $flag = $this->buildViaReflection(Flag::class, [
                'targetType' => $isPost ? 'post' : 'comment',
                'targetId' => $targetId,
                'reporterId' => $reporterId,
                'reason' => $reason,
                'status' => 'rejected',
                'resolvedById' => $moderatorId,
                'resolvedAt' => $resolvedAt,
                'createdAt' => $createdAt,
            ]);

            $flags[] = $flag;
        }

        return $flags;
    }
}
