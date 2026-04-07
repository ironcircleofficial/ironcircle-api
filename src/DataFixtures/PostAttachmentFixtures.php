<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\PostAttachmentFactory;
use App\Post\Domain\Model\Post;
use App\User\Domain\Model\User;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use function assert;

final class PostAttachmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly PostAttachmentFactory $attachmentFactory
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

        $attachments = $this->attachmentFactory->createAll($postIds, $authorIds);

        // Persist all attachments
        foreach ($attachments as $attachment) {
            $manager->persist($attachment);
        }
        $manager->flush();
    }

    /**
     * @return array<class-string>
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class, PostFixtures::class];
    }
}
