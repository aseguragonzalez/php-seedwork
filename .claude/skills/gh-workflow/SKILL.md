---
name: gh-workflow
description: Use this skill whenever working on php-seedwork and about to open a GitHub issue or pull request, or when deciding a commit message/PR title. Encodes this repo's issue-first workflow, label taxonomy, and the commit/PR conventions that keep semantic-release from shipping an unintended version bump.
version: 1.0.0
---

# php-seedwork GitHub workflow

This repo requires every change to start from an issue and follows strict Conventional
Commit rules because `semantic-release` reads commit types literally to decide whether
(and what kind of) a release ships. See `CLAUDE.md` for the policy statement this skill
implements.

## Flow

1. **Analyze the request** — understand what's being asked before writing any code.
2. **Create (or confirm) an issue** — describe the problem/request. Skip only if an
   issue for this exact change already exists.
3. **Open a PR that references it** — use a closing keyword (`Closes #N`) in the PR body,
   never just a prose mention.

Never open a PR without a linked issue.

## Labels

Use the repo's existing label set (`gh label list`) — don't invent new ones:

`bug`, `enhancement`, `documentation`, `question`, `good first issue`, `help wanted`,
`invalid`, `duplicate`, `wontfix`, `dependencies`, `php`, `released`.

## Commit type → release impact

The commit type is read by `@semantic-release/commit-analyzer` (see `.releaserc.json`).
Pick it by **which layer actually changed**, not by habit:

| Change scope | Type | Triggers release? |
|---|---|---|
| `src/` behaviour (bug fix) | `fix:` | patch |
| `src/` behaviour (new capability) | `feat:` | minor |
| `src/` breaking change (renamed class, required param added, return type changed, non-abstract → abstract, etc.) | `fix!:` / `feat!:` | major |
| `docs/`, examples, README only | `docs:` | none |
| CI/pipeline/workflow files | `ci:` | none |
| Build tooling, dependencies (non-behavioural) | `build:` | none |
| Refactor with no behaviour change | `refactor:` | none |
| Test-only changes | `test:` | none |

A mismatched type (e.g. `fix:` for a docs-only change) either ships a spurious release or
silently swallows one that should have shipped — both are defects.

## PR conventions

- Title: same Conventional Commit type as above.
- Body: exactly **What / Why / How** + **How to test**, in English, no conversation history.
- Always link the issue via a closing keyword.
- Apply matching labels from the taxonomy above.
