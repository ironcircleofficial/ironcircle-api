<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\AI\Domain\Model\AISummary;
use App\Factory\AISummaryFactory;
use App\Post\Domain\Model\Post;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use function assert;

final class AISummaryFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly AISummaryFactory $aiSummaryFactory
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        assert($manager instanceof DocumentManager);

        // Collect AI-enabled post IDs
        $aiPostIds = [];
        for ($i = 0; $i < 15; $i++) {
            try {
                /** @var Post $post */
                $post = $this->getReference('post_ai_' . $i, Post::class);
                $aiPostIds[] = $post->getId();
            } catch (\OutOfBoundsException) {
                // Not all indices may exist if fewer AI-enabled posts were created
                break;
            }
        }

        if (empty($aiPostIds)) {
            return; // No AI-enabled posts found
        }

        $summaries = $this->aiSummaryFactory->createAll($aiPostIds);

        // Persist all summaries
        foreach ($summaries as $summary) {
            $manager->persist($summary);
        }
        $manager->flush();
    }

    /**
     * @return array<class-string>
     */
    public function getDependencies(): array
    {
        return [PostFixtures::class];
    }
}
