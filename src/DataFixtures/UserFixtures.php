<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\UserFactory;
use App\User\Domain\Model\User;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use function assert;

final class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserFactory $userFactory
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        assert($manager instanceof DocumentManager);

        $users = $this->userFactory->createAll();

        foreach ($users as $user) {
            $manager->persist($user);
        }
        $manager->flush();

        foreach ($users as $user) {
            $this->addReference('user_' . $user->getUsername(), $user);
        }

        $modIndex = 0;
        foreach ($users as $user) {
            if ($user->hasRole(User::ROLE_ADMIN)) {
                $this->addReference('user_admin', $user);
            } elseif ($user->hasRole(User::ROLE_MODERATOR)) {
                $this->addReference('user_mod_' . $modIndex, $user);
                $modIndex++;
            }
        }
    }
}
