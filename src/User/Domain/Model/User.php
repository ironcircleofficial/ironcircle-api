<?php

declare(strict_types=1);

namespace App\User\Domain\Model;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'users')]
#[MongoDB\Index(keys: ['username' => 'asc'], options: ['unique' => true])]
#[MongoDB\Index(keys: ['email' => 'asc'], options: ['unique' => true])]
class User
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_MODERATOR = 'ROLE_MODERATOR';
    public const ROLE_MEMBER = 'ROLE_MEMBER';

    public const VALID_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MODERATOR,
        self::ROLE_MEMBER,
    ];

    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    private readonly string $username;

    #[MongoDB\Field(type: 'string')]
    private readonly string $email;

    #[MongoDB\Field(type: 'string')]
    private string $password;

    /**
     * @var array<string>
     */
    #[MongoDB\Field(type: 'collection')]
    private array $roles;

    #[MongoDB\Field(type: 'date_immutable')]
    private readonly DateTimeImmutable $createdAt;

    #[MongoDB\Field(type: 'date_immutable')]
    private DateTimeImmutable $updatedAt;

    /**
     * @param array<string> $roles
     */
    public function __construct(
        string $username,
        string $email,
        string $password,
        array $roles = [self::ROLE_MEMBER]
    ) {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function changePassword(string $hashedPassword): void
    {
        $this->password = $hashedPassword;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @param array<string> $roles
     */
    public function updateRoles(array $roles): void
    {
        $this->roles = $roles;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }
}
