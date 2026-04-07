<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Circle\Domain\Model\Circle;
use App\Factory\PostFactory;
use App\Post\Domain\Model\Post;
use App\User\Domain\Model\User;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use function assert;

final class PostFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly PostFactory $postFactory
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        assert($manager instanceof DocumentManager);

        // Collect author IDs from all non-moderator users
        $authorIds = [];
        for ($i = 0; $i < 25; $i++) {
            // Users from index 0 to 24 (all users created in UserFactory)
            $usernames = [
                'ironadmin', 'mod_derek', 'mod_priya', 'mod_carlos',
                'ironmike', 'squatqueen', 'kettlebell_ko', 'cardio_champ',
                'gainz_goblin', 'yoga_yoda', 'crossfit_cody', 'runner_rachel',
                'powerlifter_paul', 'strongman_sam', 'nutrition_nate', 'flexibility_fiona',
                'cyclist_chris', 'boxer_blake', 'swimmer_sophia', 'mobility_mark',
                'recovery_ruby', 'fitness_flora', 'gym_guru', 'bulk_baron',
                'cutting_chloe', 'hiit_henry',
            ];

            if ($i < \count($usernames)) {
                /** @var User $user */
                $user = $this->getReference('user_' . $usernames[$i], User::class);
                $authorIds[] = $user->getId();
            }
        }

        // Collect circle IDs
        $circleIds = [];
        for ($i = 0; $i < 100; $i++) {
            /** @var Circle $circle */
            $circle = $this->getReference('circle_' . $i, Circle::class);
            $circleIds[] = $circle->getId();
        }

        $posts = $this->postFactory->createAll($authorIds, $circleIds);

        // Persist in batches
        $batchSize = 50;
        $aiPostCount = 0;
        foreach ($posts as $i => $post) {
            $manager->persist($post);
            if (($i + 1) % $batchSize === 0) {
                $manager->flush();
            }
        }
        $manager->flush();

        // Store references for dependent fixtures
        $aiPostIndex = 0;
        foreach ($posts as $i => $post) {
            $this->addReference('post_' . $i, $post);
            // Track AI-enabled posts separately for AISummaryFixtures
            if ($post->isAiSummaryEnabled()) {
                $this->addReference('post_ai_' . $aiPostIndex, $post);
                $aiPostIndex++;
            }
        }
    }

    /**
     * @return array<class-string>
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class, CircleFixtures::class];
    }
}
