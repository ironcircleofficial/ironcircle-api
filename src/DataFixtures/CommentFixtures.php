<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Comment\Domain\Model\Comment;
use App\Factory\CommentFactory;
use App\Post\Domain\Model\Post;
use App\User\Domain\Model\User;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use function assert;

final class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly CommentFactory $commentFactory
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        assert($manager instanceof DocumentManager);

        // Collect post IDs
        $postIds = [];
        for ($i = 0; $i < 150; $i++) {
            /** @var Post $post */
            $post = $this->getReference('post_' . $i, Post::class);
            $postIds[] = $post->getId();
        }

        // Collect author IDs
        $authorIds = [];
        for ($i = 0; $i < 25; $i++) {
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

        $comments = $this->commentFactory->createAll($postIds, $authorIds);

        // Persist in batches
        $batchSize = 50;
        foreach ($comments as $i => $comment) {
            $manager->persist($comment);
            if (($i + 1) % $batchSize === 0) {
                $manager->flush();
            }
        }
        $manager->flush();

        // Store references for dependent fixtures
        // Note: Only comments without parentCommentId (top-level) are referenced
        // Threaded replies don't need explicit references since they use parentCommentId
        foreach ($comments as $i => $comment) {
            $this->addReference('comment_' . $i, $comment);
        }
    }

    /**
     * @return array<class-string>
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class, PostFixtures::class];
    }
}
