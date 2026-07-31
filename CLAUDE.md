# php-seedwork

DDD and Hexagonal Architecture building blocks for PHP.
**This is a library of abstractions, not a domain application.**
Every class is extended/implemented/composed by downstream projects — design decisions here are public contracts.

**All work happens inside the devcontainer.** PHP and Composer are not required (and must not be relied upon)
on the host — every command in this document assumes `devcontainer exec`.

## Workflow

Every change goes through three stages — never skip straight to code:

1. **Analyze and open the issue.** Understand the request, confirm the understanding with the requester,
 then open (or confirm) a GitHub issue with clear, testable acceptance criteria. No PR without a linked
 issue (e.g. `Closes #N`).
2. **Plan before implementing.** Re-read the issue, and draft an implementation plan that separates
 **code**, **tests**, and **documentation** as independent tracks built against the same agreed contracts
 (interfaces/signatures decided up front), so the tracks don't conflict with each other.
3. **Implement in parallel.** Execute the plan using parallel agents for code, tests, and documentation
 (see `.claude/agents/`) against the contracts fixed in step 2. Subagents don't share context with the
 main conversation or each other — include the fixed contract explicitly in every agent's prompt, don't
 assume they can infer it from one another's work.

Additional rules that apply throughout:

- All documentation and GitHub artifacts — issues, PRs, commit messages, code comments — are written in
 English, regardless of the language used in conversation, and are **direct and concise**: state the
 what/why/how, never the conversation or reasoning process that led to it. No narrative, no TL;DR filler.
- While analyzing any request, check whether nearby code could be improved. If so, do not bundle the
 improvement into the current change — open a separate issue for it (see the boy-scout skill).
- For bug reports, analyze the problem and propose a solution before opening an issue for it (see the
 bug-triage skill).
- PR review comments (yours or a bot reviewer's, e.g. Copilot) are answered in English, as a reply in the
 same review-comment thread — never a new top-level PR comment.
- See `.claude/skills/gh-workflow/SKILL.md` for label taxonomy, identity, reviewer, and issue/PR mechanics.

## Commands

All commands run inside the devcontainer. Start it once with:

```bash
devcontainer up --workspace-folder .
```

Then run any make target via:

```bash
devcontainer exec --workspace-folder . make <target>
```

- `make cs` / `make cs-fix` — PHP-CS-Fixer check / auto-fix
- `make stan` — PHPStan level max
- `make test` — PHPUnit with coverage; `make test-no-coverage` for the fast variant
- `make test-examples` — run the BankAccount example test suite
- `make check` — cs + stan + tests (no coverage). **Run before every commit.**
- `make all` — install + cs-fix + check

## Commit and PR conventions

Conventional Commits drive `semantic-release` (see `.releaserc.json`) — the commit type alone decides
whether (and what kind of) a release ships, so getting it wrong is not cosmetic.

- The commit **type must match the layer actually changed**: `docs:` for changes limited to `docs/` or
 examples, `ci:`/`build:` for pipeline/tooling changes, `fix:`/`feat:` only when `src/` behaviour changes.
- **Breaking changes use `!`** (`fix!:`/`feat!:`) so semantic-release cuts a major version. Any of the
 backward-compatibility violations in "Key rules" below (required param added, class renamed, return type
 changed, etc.) is a breaking change and must be marked this way — never shipped as a plain `fix:`/`feat:`.
- An optional `(scope)` is allowed and encouraged for clarity (`fix(ci):`, `build(deps):`, `docs(examples):`)
 — the scope is informational only (changelog grouping) and **never changes the release decision**; only
 the type prefix and the `!`/`BREAKING CHANGE` footer do.
- Refactors use `refactor:` and must never carry behaviour changes — a refactor must not trigger a release.
- PR titles use the same Conventional Commit type as the change they introduce.

## Pre-commit workflow

**Always** run both before committing (inside the devcontainer):
1. `devcontainer exec --workspace-folder . make all` — installs deps, fixes formatting, and runs the full check pipeline.
2. `devcontainer exec --workspace-folder . pre-commit run --all-files` — enforces JSON/YAML formatting, trailing whitespace,
   and re-runs PHP checks via the git hook.
   The conventional commit message hook runs at the `commit-msg` stage only; it is
   not triggered by `--all-files`. It is enforced automatically when you run `git commit`.

## Architecture

- `src/Domain/` — Entity, ValueObject, AggregateRoot, DomainEvent, Repository, UnitOfWork.
 **Zero external dependencies.**
- `src/Application/` — Command/Query/handlers/buses, DomainEventBus, IntegrationEvent/Publisher,
 BackgroundTask/TaskScheduler, Result/Maybe, ValidationErrors. **Depends only on Domain.**
- `src/Infrastructure/` — RegistryCommandBus/QueryBus, TransactionalCommandBus,
 DomainEventCoordinatorCommandBus, DeferredDomainEventBus, outbox patterns.
 **Only layer that may use PSR or library types.**
- `src/Testing/` — Spy interfaces and InMemory/fake implementations for use in consumer tests.
 **Must not be used in production code.**

Never leak Infrastructure or framework types into Domain or Application.

## Key rules

- PHP 8.4+, `declare(strict_types=1);` always.
- `final` on classes unless explicitly designed for extension; abstract base classes are `abstract`, never `final`.
- `readonly` properties and constructor promotion by default.
- Interfaces for contracts (Repository, CommandBus); abstract classes for shared behaviour (AggregateRoot, Entity).
- No `mixed` types without justification. Use PHPStan `@template`/`@extends` for generics.
- Exceptions: a business-rule violation gets a named `\DomainException` (PHP stdlib) subclass
 defined in the domain layer — the only correct way to signal one. Other cases use the matching
 SPL type (`\InvalidArgumentException`, `\LogicException`, ...) or propagate as-is for
 infrastructure/library exceptions; `\DomainException` is not a catch-all. Never bare `\Exception`.
- Backward compatibility matters: adding required params, renaming classes, or changing return types are breaking changes.

## Fixture and examples

- Canonical example: `docs/examples/BankAccount/` — full bounded context showing how every building
 block is used. **Read this before creating new patterns.**
- When adding a new base class or interface, add a concrete implementation in the example.
- Update consumer examples in `docs/examples/` when changes affect downstream usage.
- **Any code change (or evaluation of one) must review and update `docs/component-reference.md`,
 `docs/coding-standards.md`, and this fixture as needed** — documentation that drifts from the code it
 describes is a defect, not a follow-up.
- After touching the fixture, run `make test-examples` and re-read it end to end: it must still make
 sense as idiomatic usage, not merely compile and pass.

## Testing

- PHPUnit ^12.5, tests in `tests/`.
- Use `createMock()` to verify interactions, `createStub()` for stand-ins. Never mock domain objects — use the fixture.
- Test naming: `test{Behavior}` (camelCase) — the entire test suite uses this consistently;
 there is no snake_case variant in use.
- Edge cases matter more here than in app code — consumers depend on predictable behaviour.

## Code review

When reviewing changes, check in this order:

1. **Layer rules** — no upward dependency leaks.
2. **Contract compatibility** — public/protected signatures unchanged or backward-compatible. Flag breaking changes.
3. **API surface** — new public methods truly necessary? Interfaces lean (ISP)?
4. **Invariants** — validation lives in the value object / entity, not left to consumers.
5. **Tests** — happy path + edge cases for new/changed behaviour. Mock/stub choices correct.
6. **Docs** — `docs/component-reference.md` updated, fixture updated, examples updated.
7. **`make all` passes.**

## Reference docs

For coding standards and full component reference, see `docs/`:

- `docs/coding-standards.md` — style rules and conventions
- `docs/component-reference.md` — every interface and base class
