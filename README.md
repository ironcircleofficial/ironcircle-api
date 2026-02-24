# IronCircle API

A self-hostable, fitness-focused community platform API inspired by Reddit-style forums. Built as a graduation thesis project.

Users form or join **Circles** (communities), create **Posts** with file attachments, write **Comments** (one-level threading), **Vote** on content, **Flag** inappropriate material, and optionally generate **AI-powered TL;DR summaries**.

## Tech Stack

| Concern        | Technology                     |
|----------------|--------------------------------|
| Framework      | Symfony 6.4                    |
| Language       | PHP 8.2+                      |
| Database       | MongoDB                        |
| ODM            | Doctrine MongoDB ODM           |
| Authentication | JWT (LexikJWTAuthenticationBundle) |
| Bus            | Symfony Messenger (CQRS)       |
| Transformers   | League/Fractal                 |
| AI Provider    | HuggingFace Inference API      |

## Architecture

Domain-Driven Design (DDD) with Command Query Responsibility Segregation (CQRS).

```
src/
├── Auth/          # JWT authentication (login, register)
├── User/          # User identity, roles, profile
├── Circle/        # Communities (create, join, moderate)
├── Post/          # Content with attachments
├── Comment/       # One-level threaded comments
├── Vote/          # Upvote / downvote system
├── Moderation/    # Content flagging and review
├── Feed/          # Aggregated post feeds
├── Search/        # Full-text search across resources
├── AI/            # AI-generated post summaries
├── Admin/         # Admin-only user and circle management
└── Shared/        # Cross-cutting infrastructure (file storage)
```

Each bounded context follows:

```
<Context>/
├── Domain/          # Aggregates, repository interfaces, exceptions, events
├── Application/     # Commands, queries, handlers, DTOs
├── Infrastructure/  # Repository implementations, external clients
└── UI/Http/         # Controllers, request validators, transformers, voters
```

## Prerequisites

- PHP 8.2+
- Composer
- MongoDB 6.0+
- OpenSSL (for JWT key generation)

## Setup

```bash
# Clone the repository
git clone <repository-url>
cd ironcircle-api

# Install dependencies
composer install

# Configure environment
cp .env .env.local
```

Edit `.env.local` with your values:

```dotenv
MONGODB_URI=mongodb://localhost:27017
MONGODB_DB=ironcircle
HUGGINGFACE_API_TOKEN=hf_your_token_here
```

Generate JWT keys:

```bash
php bin/console lexik:jwt:generate-keypair
```

Start the server:

```bash
symfony server:start
# or
php -S localhost:8000 -t public
```

## Environment Variables

| Variable               | Description                      | Default                          |
|------------------------|----------------------------------|----------------------------------|
| `MONGODB_URI`          | MongoDB connection string        | `mongodb://localhost:27017`      |
| `MONGODB_DB`           | Database name                    | `ironcircle`                     |
| `JWT_SECRET_KEY`       | Path to JWT private key          | `config/jwt/private.pem`        |
| `JWT_PUBLIC_KEY`       | Path to JWT public key           | `config/jwt/public.pem`         |
| `JWT_PASSPHRASE`       | JWT key passphrase               | —                                |
| `HUGGINGFACE_API_TOKEN`| HuggingFace API token            | —                                |
| `HUGGINGFACE_MODEL`    | Summarization model              | `facebook/bart-large-cnn`       |
| `CORS_ALLOW_ORIGIN`    | Allowed CORS origins (regex)     | `localhost`                      |

## API Overview

Base URL: `/api/v1`

### Authentication

| Method | Endpoint              | Auth     | Description        |
|--------|-----------------------|----------|--------------------|
| POST   | `/auth/register`      | Public   | Register user      |
| POST   | `/auth/login`         | Public   | Login, returns JWT |

### Circles

| Method | Endpoint                      | Auth        | Description              |
|--------|-------------------------------|-------------|--------------------------|
| POST   | `/circles`                    | Required    | Create circle            |
| GET    | `/circles`                    | Public      | List circles             |
| GET    | `/circles/{id}`               | Conditional | Get circle by ID         |
| GET    | `/circles/slug/{slug}`        | Conditional | Get circle by slug       |
| PUT    | `/circles/{id}`               | Required    | Update circle            |
| PATCH  | `/circles/{id}`               | Required    | Partial update circle    |
| DELETE | `/circles/{id}`               | Required    | Delete circle            |
| PATCH  | `/circles/{id}/visibility`    | Required    | Change visibility        |

### Posts

| Method | Endpoint                           | Auth        | Description              |
|--------|------------------------------------|-------------|--------------------------|
| POST   | `/circles/{circleId}/posts`        | Required    | Create post              |
| GET    | `/circles/{circleId}/posts`        | Public      | List posts in circle     |
| GET    | `/posts/{id}`                      | Public      | Get post                 |
| PUT    | `/posts/{id}`                      | Required    | Update post              |
| PATCH  | `/posts/{id}`                      | Required    | Partial update post      |
| DELETE | `/posts/{id}`                      | Required    | Delete post              |
| PATCH  | `/posts/{id}/ai-summary`           | Required    | Toggle AI summary flag   |

### Attachments

| Method | Endpoint                           | Auth        | Description              |
|--------|------------------------------------|-------------|--------------------------|
| POST   | `/posts/{postId}/attachments`      | Required    | Upload file              |
| GET    | `/posts/{postId}/attachments`      | Public      | List attachments         |
| GET    | `/attachments/{id}/download`       | Public      | Download file            |
| DELETE | `/attachments/{id}`                | Required    | Delete attachment         |

### Comments

| Method | Endpoint                           | Auth        | Description              |
|--------|------------------------------------|-------------|--------------------------|
| POST   | `/posts/{postId}/comments`         | Required    | Create comment           |
| GET    | `/posts/{postId}/comments`         | Public      | List comments            |
| PUT    | `/comments/{id}`                   | Required    | Update comment           |
| PATCH  | `/comments/{id}`                   | Required    | Partial update comment   |
| DELETE | `/comments/{id}`                   | Required    | Delete comment           |

### Votes

| Method | Endpoint                              | Auth     | Description        |
|--------|---------------------------------------|----------|--------------------|
| POST   | `/votes`                              | Required | Cast or change vote|
| DELETE | `/votes/{targetType}/{targetId}`      | Required | Retract vote       |

### Moderation

| Method | Endpoint                              | Auth           | Description        |
|--------|---------------------------------------|----------------|--------------------|
| POST   | `/flags`                              | Required       | Flag content       |
| GET    | `/moderation/flags`                   | Moderator/Admin| List pending flags |
| PATCH  | `/moderation/flags/{id}/resolve`      | Moderator/Admin| Resolve flag       |
| PATCH  | `/moderation/flags/{id}/dismiss`      | Moderator/Admin| Dismiss flag       |

### Feed

| Method | Endpoint                        | Auth        | Description              |
|--------|---------------------------------|-------------|--------------------------|
| GET    | `/feed`                         | Public      | Global feed              |
| GET    | `/feed/circles/{circleId}`      | Conditional | Circle feed              |

### Search

| Method | Endpoint            | Auth   | Description        |
|--------|---------------------|--------|--------------------|
| GET    | `/search/circles`   | Public | Search circles     |
| GET    | `/search/posts`     | Public | Search posts       |
| GET    | `/search/users`     | Public | Search users       |

### AI Summaries

| Method | Endpoint                              | Auth        | Description              |
|--------|---------------------------------------|-------------|--------------------------|
| GET    | `/posts/{id}/ai-summary`              | Conditional | Get cached summary       |
| POST   | `/posts/{id}/ai-summary/generate`     | Required    | Generate summary         |

### Admin

| Method | Endpoint                        | Auth  | Description            |
|--------|---------------------------------|-------|------------------------|
| GET    | `/admin/users`                  | Admin | List all users         |
| GET    | `/admin/users/{id}`             | Admin | Get user details       |
| PATCH  | `/admin/users/{id}/roles`       | Admin | Update user roles      |
| DELETE | `/admin/users/{id}`             | Admin | Delete user            |
| GET    | `/admin/circles`                | Admin | List all circles       |
| DELETE | `/admin/circles/{id}`           | Admin | Force-delete circle    |

## Roles

| Role             | Capabilities                                              |
|------------------|-----------------------------------------------------------|
| `ROLE_MEMBER`    | Create circles/posts/comments, vote, flag, generate AI    |
| `ROLE_MODERATOR` | All member abilities + manage content, review flags       |
| `ROLE_ADMIN`     | All abilities + admin panel, force-delete, manage roles   |

## Pagination

All list endpoints support `?limit` (1–100, default 20) and `?offset` (min 0, default 0).

```json
{
  "items": [],
  "pagination": { "total": 42, "limit": 20, "offset": 0 }
}
```

## Error Format

```json
{
  "error": "NOT_FOUND",
  "message": "Circle with ID xyz was not found"
}
```

## Testing

```bash
php bin/phpunit
```

## API Documentation

Full OpenAPI 3.1 specification available at [openapi.json](openapi.json).

## License

This project is part of a graduation thesis and is not licensed for public distribution.
