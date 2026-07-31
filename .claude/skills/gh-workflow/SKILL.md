---
name: gh-workflow
description: Use this skill whenever working on php-seedwork and about to open a GitHub issue or pull request, decide a commit message/PR title, or plan how to implement an issue. Encodes this repo's issue-first workflow, identity rules, label taxonomy, and the commit/PR conventions that keep semantic-release from shipping an unintended version bump.
version: 1.2.0
---

# php-seedwork GitHub workflow

This repo requires every change to start from an issue and follows strict Conventional
Commit rules because `semantic-release` reads commit types literally to decide whether
(and what kind of) a release ships. See `CLAUDE.md` for the policy statement this skill
implements.

## Flow

1. **Analyze and open the issue.** Understand the request, confirm the understanding with
   the requester, then create (or confirm) an issue with clear, testable acceptance
   criteria. Skip creation only if an issue for this exact change already exists.
2. **Plan.** Re-read the issue and draft an implementation plan that separates code,
   tests, and documentation as independent tracks against contracts (interfaces/signatures)
   agreed up front.
3. **Implement in parallel** using the code/test/docs agents under `.claude/agents/`
   against those contracts.
4. **Open a PR that references the issue** — use a closing keyword (`Closes #N`) in the
   PR body, never just a prose mention.

Never open a PR without a linked issue. While analyzing any request, also check whether
nearby code could be improved — if so, open a **separate** issue for it (see the
`boy-scout` skill) rather than folding it into this change.

## Identity

- **Issues** are created under the requester's own `gh` session (the default — do **not**
  export `GH_TOKEN`). Issues represent the requester's decisions/requests.
- **Commits and PRs** use the bot App identity when configured (see the global
  `~/.claude/CLAUDE.md` instructions for minting `GH_TOKEN` and commit authorship) — this
  is what lets the requester leave a genuine "Approve" review, since GitHub blocks a PR
  author from approving their own PR.
- Never mix the two: don't create an issue with the bot token, and don't commit/push with
  the requester's personal session when the bot identity is available.

## Reviewers

Every PR must explicitly request review from the repo owner
(`gh pr create --reviewer <owner>` or `gh pr edit <n> --add-reviewer <owner>`), even though
`.github/CODEOWNERS` may also trigger an automatic request — make it explicit rather than
relying on that silently.

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

An optional `(scope)` is allowed and encouraged (`fix(ci):`, `build(deps):`,
`docs(examples):`) — it's informational only, for changelog grouping. It **never** changes
the release decision; only the type prefix and `!`/`BREAKING CHANGE` do. Dependabot's
`build(deps):` is a working example of this: `build:` never releases, regardless of scope.

## Issue and PR content

- Body: exactly **What / Why / How** + **How to test**, in English.
- **No conversation narrative or reasoning trail** — state the outcome directly. Nobody
  reads a TL;DR of how the conclusion was reached; write the conclusion.
- Always link the issue via a closing keyword.
- Apply matching labels from the taxonomy above.
- Request review from the repo owner explicitly (see Reviewers above).

## Replying to PR review comments

Answer review comments — from a human or a bot reviewer (e.g. Copilot) — in English, as a
**reply in the same review-comment thread**, never as a new top-level PR comment. A fresh
top-level comment detaches the answer from the specific line/finding it addresses.

```bash
gh api repos/<owner>/<repo>/pulls/<pr-number>/comments \
  -f body="<answer in English>" \
  -F in_reply_to=<review_comment_id>
```

Get `<review_comment_id>` from `gh api repos/<owner>/<repo>/pulls/<pr-number>/comments`
(the `id` field of the comment being answered).
