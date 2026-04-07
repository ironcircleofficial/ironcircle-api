<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Circle\Domain\Model\Circle;
use App\DataFixtures\Concern\TimestampBackdateTrait;
use App\Factory\CircleFactory;
use App\User\Domain\Model\User;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Instantiator\Instantiator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use ReflectionClass;
use function assert;

final class CircleFixtures extends Fixture implements DependentFixtureInterface
{
    use TimestampBackdateTrait;

    public function __construct(
        private readonly CircleFactory $circleFactory
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        assert($manager instanceof DocumentManager);

        /** @var User $admin */
        $admin = $this->getReference('user_admin', User::class);
        $adminId = $admin->getId();

        // Get moderator user IDs
        $moderatorIds = [];
        for ($i = 0; $i < 3; $i++) {
            /** @var User $mod */
            $mod = $this->getReference('user_mod_' . $i, User::class);
            $moderatorIds[] = $mod->getId();
        }

        $circles = $this->circleFactory->createAll($adminId);

        // Assign moderators to some circles (every 10th circle gets all 3 mods)
        foreach ($circles as $i => $circle) {
            if ($i % 10 === 0) {
                // Rebuild circle with moderators using Instantiator
                $instantiator = new Instantiator();
                $newCircle = $instantiator->instantiate(Circle::class);
                $ref = new ReflectionClass(Circle::class);

                $fields = ['name', 'slug', 'description', 'visibility', 'creatorId', 'moderatorIds', 'createdAt', 'updatedAt'];
                foreach ($fields as $fieldName) {
                    $prop = $ref->getProperty($fieldName);
                    $prop->setAccessible(true);
                    if ($fieldName === 'moderatorIds') {
                        $prop->setValue($newCircle, $moderatorIds);
                    } else {
                        $value = match ($fieldName) {
                            'name' => $circle->getName(),
                            'slug' => $circle->getSlug(),
                            'description' => $circle->getDescription(),
                            'visibility' => $circle->getVisibility(),
                            'creatorId' => $circle->getCreatorId(),
                            'createdAt' => $circle->getCreatedAt(),
                            'updatedAt' => $circle->getUpdatedAt(),
                            default => null,
                        };
                        $prop->setValue($newCircle, $value);
                    }
                }
                $circles[$i] = $newCircle;
            }
        }

        // Persist in batches
        $batchSize = 50;
        foreach ($circles as $i => $circle) {
            $manager->persist($circle);
            if (($i + 1) % $batchSize === 0) {
                $manager->flush();
            }
        }
        $manager->flush();

        // Store references for dependent fixtures
        foreach ($circles as $i => $circle) {
            $this->addReference('circle_' . $i, $circle);
        }
    }

    /**
     * @return array<class-string>
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
