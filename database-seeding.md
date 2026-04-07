# Database Seeding

## Overview

This document describes the database seeding system for IronCircle. The system populates all MongoDB collections with realistic fitness-community data, enabling comprehensive demonstration of application features.

## Architecture

The seeding system is built using:
- **Doctrine Data Fixtures** for fixture orchestration and dependency management
- **Factory pattern** for data generation with type safety
- **Doctrine\Instantiator** for constructing readonly domain objects with historical timestamps

### Data Generation Strategy

Factories generate domain objects with realistic attributes:
- Fitness-themed usernames and circle topics
- Distributed timestamps across 180 days of history
- Unique constraints enforced before persistence
- Proper foreign key relationships via ObjectId strings

Fixtures coordinate the creation and persistence workflow:
- Dependencies declared via `getDependencies()`
- Automatic topological ordering by the Doctrine framework
- Reference sharing using `addReference()` for inter-fixture access
- Batched persistence (50 records per batch) for performance

### Seeded Collections

| Collection | Records | Scope |
|---|---|---|
| users | 26 | 1 admin, 3 moderators, 22 members |
| circles | 100 | Diverse fitness topics |
| posts | 150 | Community discussions |
| comments | 300 | Threaded discussions |
| votes | 500 | Engagement signals |
| flags | 30 | Moderation workflow |
| post_attachments | 20 | File metadata |
| ai_summaries | 15 | AI feature demonstration |

Total: **1,141 documents**

## Installation

### Prerequisites

Ensure MongoDB 6.0+ is running:

```bash
mongod
```

Verify Symfony environment configuration in `.env.local`:

```dotenv
MONGODB_URI=mongodb://localhost:27017
MONGODB_DB=ironcircle
```

### Running the Seeder

```bash
php bin/console doctrine:mongodb:fixtures:load --no-interaction
```

This command:
1. Purges existing collections
2. Loads fixtures in dependency order
3. Persists 1,141 documents
4. Creates all indexes

Expected output:
```
> purging database
> loading App\DataFixtures\UserFixtures
> loading App\DataFixtures\CircleFixtures
> loading App\DataFixtures\PostFixtures
> loading App\DataFixtures\PostAttachmentFixtures
> loading App\DataFixtures\CommentFixtures
> loading App\DataFixtures\FlagFixtures
> loading App\DataFixtures\VoteFixtures
> loading App\DataFixtures\AISummaryFixtures
```

### Verification

Check record counts:

```bash
php -r "
\$client = new \MongoDB\Client('mongodb://localhost:27017');
\$db = \$client->ironcircle;
echo 'Users: ' . \$db->users->countDocuments([]) . \"\n\";
echo 'Circles: ' . \$db->circles->countDocuments([]) . \"\n\";
echo 'Posts: ' . \$db->posts->countDocuments([]) . \"\n\";
echo 'Comments: ' . \$db->comments->countDocuments([]) . \"\n\";
echo 'Votes: ' . \$db->votes->countDocuments([]) . \"\n\";
echo 'Flags: ' . \$db->flags->countDocuments([]) . \"\n\";
echo 'Attachments: ' . \$db->post_attachments->countDocuments([]) . \"\n\";
echo 'AI Summaries: ' . \$db->ai_summaries->countDocuments([]) . \"\n\";
"
```

Expected output:
```
Users: 26
Circles: 100
Posts: 150
Comments: 300
Votes: 500
Flags: 30
Attachments: 20
AI Summaries: 15
```

## Seeded Data

### User Accounts

**Admin:** `ironadmin` / `IronCircle2024!`
- Full system access
- Performs admin operations

**Moderators (3):** `mod_derek`, `mod_priya`, `mod_carlos`
- Moderation queue access
- Can resolve flags
- Can assign roles

**Members (22):** Fitness-themed usernames
- Standard user permissions
- Can create posts, comment, vote

All accounts use the shared password `IronCircle2024!`.

### Circles

100 fitness-focused communities covering:
- Strength: Powerlifting, Olympic Lifting, Strongman
- Conditioning: HIIT, Endurance, Running
- Specialties: Yoga, Calisthenics, CrossFit, Boxing, Swimming
- Nutrition: Keto, Macro Counting, Meal Prep, Vegan
- Recovery: Mobility, Sleep, Stretching
- Anatomy: Legs, Chest, Back, Core, Glutes

All public visibility. Every 10th circle has 3 moderators assigned.

### Posts

150 discussion posts with realistic fitness content:
- Form check requests
- Nutrition advice
- Programming questions
- Progress reports
- Timestamps: 0-180 days ago
- 15 have `aiSummaryEnabled=true`

### Comments

300 comments with nested threading:
- 210 top-level responses
- 90 replies to existing comments
- Realistic fitness community dialogue

### Votes

500 votes with realistic distribution:
- 400 upvotes (80%)
- 100 downvotes (20%)
- Unique per user/target pair

### Flags

30 moderation flags showing complete workflow:
- 10 pending
- 10 approved
- 10 rejected

Each flag includes moderator ID and resolution timestamp.

### Post Attachments

20 file attachments (metadata only, no actual files):
- Videos: mp4, mov
- Images: jpg, png
- Documents: pdf
- Realistic file sizes: 500KB to 50MB

### AI Summaries

15 generated summaries for demonstration:
- Model: facebook/bart-large-cnn
- One per post with `aiSummaryEnabled=true`
- Realistic abstractive summaries

## Design Decisions

### Readonly Property Initialization

Domain models use `private readonly` properties set only in constructors. To assign historical timestamps, the system uses `Doctrine\Instantiator` to create objects without invoking constructors, then sets fields via Reflection. This approach mirrors Doctrine ODM's hydration behavior.

### Dependency Graph

```
UserFixtures
  ↓
CircleFixtures
  ↓
PostFixtures
  ├─ PostAttachmentFixtures
  ├─ CommentFixtures
  │  ├─ VoteFixtures
  │  └─ FlagFixtures
  └─ AISummaryFixtures
```

### Unique Constraints

- Username and email per user
- Slug per circle
- One vote per (user, target_type, target_id) tuple
- One AI summary per post

## Resetting

To re-seed the database after modifications:

```bash
php bin/console doctrine:mongodb:fixtures:load --no-interaction
```

This purges all collections and reloads fixtures automatically.

## Troubleshooting

### Connection Error

Ensure MongoDB is running:

```bash
brew services start mongodb-community  # macOS
# or
mongod  # Manual start
```

### Duplicate Key Error

The `--no-interaction` flag automatically purges collections. Use without this flag only if appending to existing data.

### Missing References

Verify all fixture classes are in `src/DataFixtures/` with correct dependency declarations. Run:

```bash
php bin/console cache:clear
```

## File Structure

```
src/
├── DataFixtures/
│   ├── Concern/
│   │   └── TimestampBackdateTrait.php
│   ├── UserFixtures.php
│   ├── CircleFixtures.php
│   ├── PostFixtures.php
│   ├── PostAttachmentFixtures.php
│   ├── CommentFixtures.php
│   ├── VoteFixtures.php
│   ├── FlagFixtures.php
│   └── AISummaryFixtures.php
└── Factory/
    ├── UserFactory.php
    ├── CircleFactory.php
    ├── PostFactory.php
    ├── PostAttachmentFactory.php
    ├── CommentFactory.php
    ├── VoteFactory.php
    ├── FlagFactory.php
    └── AISummaryFactory.php
```

## Code Quality

All fixtures and factories adhere to:
- PSR-12 coding standards
- `declare(strict_types=1)` type safety
- Constructor-based dependency injection
- Immutable property declaration where applicable
