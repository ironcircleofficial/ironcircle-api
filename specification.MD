# IronCircle – Backend API Specification (Graduation Thesis)

## 1. Purpose & Scope

This document is the **single source of truth** for implementing the IronCircle backend API for the graduation thesis.

It is intentionally:

* **Exhaustive and explicit**
* **Optimized for AI agents** (GitHub Copilot, Claude Haiku / Sonnet 4.5 / Opus)
* **Strict about best practices**
* **Designed to be copied directly into the backend repository** as internal documentation

This specification applies **only to the backend API**. Frontend concerns are referenced only where they influence API design.

No shortcuts are allowed. Every solution must favor:

* Long-term maintainability
* Explicitness over magic
* Community-accepted Symfony and PHP standards

---

## 2. High-Level Concept

**IronCircle** is a self-hostable, fitness-focused community platform inspired by Reddit-style forums.

Core ideas:

* Users form or join **Circles** (communities)
* Circles contain **Posts**
* Posts contain **one-level Comments**
* Users can **vote**, **flag**, and **moderate** content
* The system provides **AI-generated TL;DR summaries** for posts (optional, cached, toggleable)

The backend is a **pure API** built with Symfony and MongoDB, following **DDD + CQRS** principles.

---

## 3. Non‑Negotiable Engineering Principles

### 3.1 General

* No fat controllers
* No anemic domain models
* No business logic in controllers
* No repository leakage into controllers
* No direct ODM usage outside repositories

### 3.2 PHP & Symfony

* PHP 8.2+
* Symfony 6.4 or 7.x
* Constructor property promotion
* `readonly` where applicable
* Strict typing everywhere
* Commands **never return values**
* Queries **never mutate state**

### 3.3 Architecture

* DDD-inspired layered architecture
* CQRS (Command / Query separation)
* Symfony Messenger as command & query bus
* Voters for authorization
* DTOs + Transformers for API output
* Validators for input

### 3.4 Documentation & Code Communication

* **Code is the primary documentation** - Write self-explanatory, clean code
* **No separate documentation files** - Never create .MD, .TXT, or .SH files to explain implementations
* **Minimal comments** - Code should be self-documenting; avoid verbose docblocks
* **Essential PHPDoc only** - Use PHPDoc only for array types (`@var array<string>`, `@return array<int, User>`)
* **Explain in chat** - When AI assistance is needed, provide explanations directly in the conversation, not in files
* **Exception**: This specification.MD file is the only allowed documentation file

---

## 4. Technology Stack

```json
{
  "backend": {
    "framework": "Symfony 6.4+",
    "language": "PHP 8.2+",
    "database": "MongoDB",
    "odm": "Doctrine MongoDB ODM",
    "auth": "LexikJWTAuthenticationBundle",
    "bus": "Symfony Messenger",
    "serialization": "Symfony Serializer",
    "validation": "Symfony Validator",
    "api_docs": "OpenAPI / Nelmio",
    "ai": "OpenAI API (server-side only)"
  }
}
```

---

## 5. Bounded Contexts

```json
[
  "Auth",
  "User",
  "Circle",
  "Post",
  "Comment",
  "Vote",
  "Moderation",
  "Feed",
  "Search",
  "AI",
  "Admin"
]
```

Each context is isolated at the **domain level**, but lives in a single Symfony application.

---

## 6. Project Structure

```
src/
 ├── Auth/
 ├── User/
 ├── Circle/
 ├── Post/
 ├── Comment/
 ├── Vote/
 ├── Moderation/
 ├── Feed/
 ├── Search/
 ├── AI/
 ├── Admin/
 ├── Shared/
```

Each bounded context internally follows the same structure.

---

## 7. Standard Module Layout (MANDATORY)

```
<Context>/
 ├── Domain/
 │   ├── Model/
 │   ├── Repository/
 │   ├── Event/
 │   └── Exception/
 ├── Application/
 │   ├── Command/
 │   ├── CommandHandler/
 │   ├── Query/
 │   ├── QueryHandler/
 │   └── DTO/
 ├── Infrastructure/
 │   ├── Persistence/
 │   ├── ODM/
 │   └── Repository/
 └── UI/
     ├── Http/
     │   ├── Controller/
     │   ├── Request/
     │   ├── Transformer/
     │   └── Voter/
```

---

## 8. Authentication & Authorization

### 8.1 Authentication

* JWT-based authentication
* Stateless API
* Access token only (no refresh token for MVP)

### 8.2 Roles

```json
["ROLE_ADMIN", "ROLE_MODERATOR", "ROLE_MEMBER", "ROLE_GUEST"]
```

### 8.3 Authorization

* Symfony Voters
* One voter per aggregate action
* No role checks in controllers

---

## 9. Core Domains

### 9.1 User

Responsibilities:

* Identity
* Roles
* Profile metadata

Key rules:

* Users are immutable except for profile updates
* Roles are controlled by admins only

---

### 9.2 Circle

Responsibilities:

* Creation
* Join / leave
* Visibility

Rules:

* Public or private
* Moderators manage content

---

### 9.3 Post

Responsibilities:

* Markdown content
* Images via storage adapter
* AI summary toggle

Rules:

* Posts belong to exactly one Circle
* Author cannot vote on own post

---

### 9.4 Comment

Responsibilities:

* One-level threading only

Rules:

* No nested replies beyond one level

---

### 9.5 Vote

Responsibilities:

* Upvote / downvote

Rules:

* One vote per user per target
* Changing vote overwrites previous

---

### 9.6 Moderation

Responsibilities:

* Flagging
* Review queue

Rules:

* Flags are immutable
* Moderators resolve flags

---

### 9.7 AI

Responsibilities:

* Generate TL;DR summaries
* Cache summaries

Rules:

* Disabled by default
* Must never block main request

---

## 10. API Design Rules

* RESTful endpoints
* Versioned under `/api/v1`
* JSON only
* No HAL, no GraphQL

Example:

```
POST /api/v1/circles
GET  /api/v1/circles/{id}
```

---

## 11. Error Handling

* Domain exceptions mapped to HTTP errors
* No leaking stack traces

Standard error format:

```json
{
  "error": "VALIDATION_ERROR",
  "message": "Title must not be empty"
}
```

---

## 12. Testing Strategy

* Unit tests for domain
* Integration tests for handlers
* Minimal controller tests

---

## 13. Git Commit Rules (MANDATORY)

* Present tense
* Imperative mood
* Short subject (≤ 72 chars)
* Optional description

Example:

```
Add circle creation command and handler

Implements validated circle creation flow using CQRS and domain rules
```

---

## 14. Development Plan (High Level)

```json
[
  "Bootstrap Symfony project",
  "Configure MongoDB ODM",
  "Implement Auth & User",
  "Implement Circle domain",
  "Implement Post & Comment",
  "Implement Voting",
  "Implement Moderation",
  "Implement Feeds & Search",
  "Implement AI summaries",
  "Add OpenAPI docs",
  "Harden security & tests"
]
```

---

## 15. Final Notes for AI Agents

* Always prefer explicitness over cleverness
* Never bypass domain rules
* Never put logic in controllers
* Respect naming conventions strictly
* Follow this document even if Symfony allows an easier way

## 16. MUST ADHERE
* Please make sure to never generate .TXT or .MD files that explain the solution you have proposed

This document defines **what correct means** for IronCircle.
