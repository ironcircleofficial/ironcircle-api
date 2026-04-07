<?php

declare(strict_types=1);

namespace App\Factory;

use App\AI\Domain\Model\AISummary;
use App\DataFixtures\Concern\TimestampBackdateTrait;

final class AISummaryFactory
{
    use TimestampBackdateTrait;

    private const MODEL = 'facebook/bart-large-cnn';

    /** @var array<string> */
    private const SUMMARIES = [
        'The author shares their recent deadlift PR and requests form feedback on the final reps. They express confidence in their pull-off mechanics and are seeking advice on improving their lockout position.',
        'This post covers pre-workout nutrition strategies, specifically recommending brown rice with chicken breast 90 minutes before training. The author emphasizes digestive tolerance as a key factor in timing.',
        'The author explores the connection between sleep deprivation and training performance, noting they\'ve been averaging 5-6 hours nightly over 8 weeks while experiencing strength stalls. They seek advice on improving sleep quality.',
        'A celebration post detailing the achievement of a first muscle-up after 6 months of consistent training. The author attributes success to progressive overload, patience, and consistent effort.',
        'This discusses creatine supplementation for endurance athletes, suggesting that benefits extend beyond strength training to include performance and recovery improvements.',
        'The author compares two programming approaches: RPE-based versus percentage-based training, discussing the challenges of adapting to RPE during fatigued states.',
        'A comprehensive journey narrative from sedentary office work to completing a 5K race within a year. The author emphasizes gradual progression and listening to body signals.',
        'Details a macro cycling strategy for cutting: 200P/250C/60F on training days and 200P/150C/100F on rest days. The author reports feeling energized and maintaining muscle during the deficit.',
        'Describes a specific stretching routine that dramatically improved squat depth within 4 weeks. The author performed the routine 5 times weekly and saw significant mobility gains.',
        'Discusses the benefits of planned deload weeks every 6-8 weeks, noting that recovery periods actually resulted in subsequent strength increases rather than being wasted time.',
        'Documents the recovery journey from shoulder impingement through physical therapy and exercise modifications. The author reports 80% improvement in symptoms.',
        'Examines the relationship between protein intake timing and recovery, noting personal observations of better recovery with immediate post-workout whey consumption.',
        'Details a hybrid training approach combining 6x3 rep schemes on main lifts with 12x3 on accessories. The author discusses the trade-off between strength progression rate and muscle gain.',
        'An experience report on intermittent fasting for endurance athletes, noting excellent energy during workouts but slightly reduced recovery compared to traditional eating patterns.',
        'Summarizes a month-long sleep tracking experiment achieving 8.5+ hours nightly, documenting improvements in recovery capacity, racing performance, and overall mood.',
    ];

    /**
     * @param array<string> $postIds Post IDs with aiSummaryEnabled=true
     * @return AISummary[]
     */
    public function createAll(array $postIds): array
    {
        $summaries = [];

        foreach ($postIds as $i => $postId) {
            $summaryText = self::SUMMARIES[$i % \count(self::SUMMARIES)];
            $createdAt = $this->randomPastDate(180);

            /** @var AISummary $summary */
            $summary = $this->buildViaReflection(AISummary::class, [
                'postId' => $postId,
                'summary' => $summaryText,
                'model' => self::MODEL,
                'generatedAt' => $createdAt,
            ]);

            $summaries[] = $summary;
        }

        return $summaries;
    }
}
