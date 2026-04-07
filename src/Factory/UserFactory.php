<?php

declare(strict_types=1);

namespace App\Factory;

use App\DataFixtures\Concern\TimestampBackdateTrait;
use App\User\Domain\Model\User;
use App\User\Infrastructure\Security\SecurityUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFactory
{
    use TimestampBackdateTrait;

    private const PLAIN_PASSWORD = 'IronCircle2024!';

    private const USERS = [
        ['username' => 'ironadmin', 'email' => 'admin@ironcircle.app', 'roles' => [User::ROLE_ADMIN]],
        ['username' => 'mod_derek', 'email' => 'derek@ironcircle.app', 'roles' => [User::ROLE_MODERATOR]],
        ['username' => 'mod_priya', 'email' => 'priya@ironcircle.app', 'roles' => [User::ROLE_MODERATOR]],
        ['username' => 'mod_carlos', 'email' => 'carlos@ironcircle.app', 'roles' => [User::ROLE_MODERATOR]],
        ['username' => 'ironmike', 'email' => 'mike@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'squatqueen', 'email' => 'sarah@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'kettlebell_ko', 'email' => 'ko@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'cardio_champ', 'email' => 'ace@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'gainz_goblin', 'email' => 'greg@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'yoga_yoda', 'email' => 'yogi@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'crossfit_cody', 'email' => 'cody@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'runner_rachel', 'email' => 'rachel@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'powerlifter_paul', 'email' => 'paul@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'strongman_sam', 'email' => 'sam@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'nutrition_nate', 'email' => 'nate@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'flexibility_fiona', 'email' => 'fiona@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'cyclist_chris', 'email' => 'chris@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'boxer_blake', 'email' => 'blake@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'swimmer_sophia', 'email' => 'sophia@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'mobility_mark', 'email' => 'mark@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'recovery_ruby', 'email' => 'ruby@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'fitness_flora', 'email' => 'flora@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'gym_guru', 'email' => 'guru@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'bulk_baron', 'email' => 'baron@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'cutting_chloe', 'email' => 'chloe@example.com', 'roles' => [User::ROLE_MEMBER]],
        ['username' => 'hiit_henry', 'email' => 'henry@example.com', 'roles' => [User::ROLE_MEMBER]],
    ];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function createAll(): array
    {
        $users = [];

        foreach (self::USERS as $data) {
            $tempUser = new SecurityUser(
                id: '',
                username: $data['username'],
                password: '',
                roles: $data['roles']
            );

            $hashedPassword = $this->passwordHasher->hashPassword($tempUser, self::PLAIN_PASSWORD);
            $createdAt = $this->randomPastDate(180);

            $user = $this->buildViaReflection(User::class, [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'roles' => $data['roles'],
                'createdAt' => $createdAt,
                'updatedAt' => $createdAt->modify('+' . random_int(1, 168) . ' hours'),
            ]);

            $users[] = $user;
        }

        return $users;
    }
}
