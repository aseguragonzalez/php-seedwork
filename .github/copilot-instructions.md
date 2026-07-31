# Copilot instructions — php-seedwork

This repository's authoritative agent/contributor instructions live in
[`CLAUDE.md`](../CLAUDE.md) at the repository root. Read it first — it covers the
architecture, workflow (issue-first, English-only artifacts), commit/PR conventions,
key coding rules, testing, and the code review checklist.

This file previously duplicated that content and drifted out of sync with it (stale
`make` targets such as `make format`/`make lint`/`make static-analyse`, and references
to classes such as `ValidationCommandBus`/`ValidationQueryBus` that no longer exist).
To avoid that happening again, it now only points to the single source of truth
instead of maintaining a second copy.

Additional references:

- [`docs/coding-standards.md`](../docs/coding-standards.md) — style rules and conventions.
- [`docs/component-reference.md`](../docs/component-reference.md) — every interface and base class.
- [`docs/examples/BankAccount/`](../docs/examples/BankAccount/) — canonical usage example.
- [`.github/CONTRIBUTING.md`](CONTRIBUTING.md) — contributor setup and PR process.
