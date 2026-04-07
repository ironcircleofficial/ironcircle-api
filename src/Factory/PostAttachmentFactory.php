<?php

declare(strict_types=1);

namespace App\Factory;

use App\DataFixtures\Concern\TimestampBackdateTrait;
use App\Post\Domain\Model\PostAttachment;

final class PostAttachmentFactory
{
    use TimestampBackdateTrait;

    /** @var array<array{original: string, mime: string, size: int}> */
    private const ATTACHMENTS = [
        ['original' => 'form_check_squat.mp4', 'mime' => 'video/mp4', 'size' => 52428800],
        ['original' => 'deadlift_pr.mp4', 'mime' => 'video/mp4', 'size' => 47185920],
        ['original' => 'before_after.jpg', 'mime' => 'image/jpeg', 'size' => 2097152],
        ['original' => 'gym_setup.jpg', 'mime' => 'image/jpeg', 'size' => 3145728],
        ['original' => 'workout_plan.pdf', 'mime' => 'application/pdf', 'size' => 1048576],
        ['original' => 'nutrition_chart.png', 'mime' => 'image/png', 'size' => 524288],
        ['original' => 'progress_graph.png', 'mime' => 'image/png', 'size' => 614400],
        ['original' => 'supplement_review.mp4', 'mime' => 'video/mp4', 'size' => 41943040],
        ['original' => 'mobility_routine.mov', 'mime' => 'video/quicktime', 'size' => 38654705],
        ['original' => 'meal_prep.jpg', 'mime' => 'image/jpeg', 'size' => 2621440],
        ['original' => 'training_split.png', 'mime' => 'image/png', 'size' => 512000],
        ['original' => 'running_stats.png', 'mime' => 'image/png', 'size' => 614400],
        ['original' => 'form_analysis.mp4', 'mime' => 'video/mp4', 'size' => 44040192],
        ['original' => 'recovery_tips.pdf', 'mime' => 'application/pdf', 'size' => 921600],
        ['original' => 'strength_gains.jpg', 'mime' => 'image/jpeg', 'size' => 1835008],
        ['original' => 'transformation.jpg', 'mime' => 'image/jpeg', 'size' => 4194304],
        ['original' => 'equipment_demo.mp4', 'mime' => 'video/mp4', 'size' => 48234496],
        ['original' => 'exercise_tutorial.mov', 'mime' => 'video/quicktime', 'size' => 35791872],
        ['original' => 'diet_breakdown.png', 'mime' => 'image/png', 'size' => 768000],
        ['original' => 'workout_video.mp4', 'mime' => 'video/mp4', 'size' => 56623104],
    ];

    /**
     * @param array<string> $postIds Post IDs to select from
     * @param array<string> $authorIds User IDs to select from
     * @return PostAttachment[]
     */
    public function createAll(array $postIds, array $authorIds): array
    {
        $attachments = [];

        foreach (self::ATTACHMENTS as $data) {
            $postId = $postIds[array_rand($postIds)];
            $authorId = $authorIds[array_rand($authorIds)];
            $createdAt = $this->randomPastDate(180);

            // Generate a unique stored filename based on timestamp and random suffix
            $storedFilename = 'attachment_' . time() . '_' . random_int(10000, 99999) . '.' . pathinfo($data['original'], \PATHINFO_EXTENSION);

            /** @var PostAttachment $attachment */
            $attachment = $this->buildViaReflection(PostAttachment::class, [
                'postId' => $postId,
                'authorId' => $authorId,
                'originalFilename' => $data['original'],
                'storedFilename' => $storedFilename,
                'mimeType' => $data['mime'],
                'size' => $data['size'],
                'uploadedAt' => $createdAt,
            ]);

            $attachments[] = $attachment;
        }

        return $attachments;
    }
}
