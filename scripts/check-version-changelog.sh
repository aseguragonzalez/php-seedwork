#!/usr/bin/env bash
# Check that VERSION and CHANGELOG.md exist, are well-formed, and stay in sync.
# Optional: when CHECK_UPDATED=1 and BASE_REF are set, require VERSION or CHANGELOG.md
# to be updated in the same PR when src/ or composer.json changed.
set -euo pipefail

REPO_ROOT="${REPO_ROOT:-$(cd "$(dirname "$0")/.." && pwd)}"
cd "$REPO_ROOT"

VERSION_FILE="$REPO_ROOT/VERSION"
CHANGELOG_FILE="$REPO_ROOT/CHANGELOG.md"

# Semver-like: X.Y.Z or X.Y.Z-prerelease (no leading v)
SEMVER_PATTERN='^[0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9.-]+)?$'

err() {
  echo "ERROR: $*" >&2
}

# --- 1. VERSION exists and is valid ---
if [ ! -f "$VERSION_FILE" ]; then
  err "VERSION file not found at $VERSION_FILE"
  exit 1
fi

VERSION_CONTENT=$(head -n1 "$VERSION_FILE" | tr -d '[:space:]')
if [ -z "$VERSION_CONTENT" ]; then
  err "VERSION file is empty"
  exit 1
fi

if ! echo "$VERSION_CONTENT" | grep -qE "$SEMVER_PATTERN"; then
  err "VERSION must be a single line matching X.Y.Z or X.Y.Z-prerelease (got: $VERSION_CONTENT)"
  exit 1
fi

# --- 2. CHANGELOG.md exists and has required structure ---
if [ ! -f "$CHANGELOG_FILE" ]; then
  err "CHANGELOG.md not found at $CHANGELOG_FILE"
  exit 1
fi

if ! grep -q '## \[Unreleased\]' "$CHANGELOG_FILE"; then
  err "CHANGELOG.md must contain a section '## [Unreleased]'"
  exit 1
fi

# First versioned section: ## [X.Y.Z] or ## [X.Y.Z-suffix] (excluding [Unreleased])
CHANGELOG_VERSION=$(
  grep -E '^## \[' "$CHANGELOG_FILE" | sed -n 's/^## \[\([^]]*\)\].*/\1/p' | while read -r v; do
    if [ "$v" != "Unreleased" ] && echo "$v" | grep -qE "$SEMVER_PATTERN"; then
      echo "$v"
      break
    fi
  done
)

if [ -z "$CHANGELOG_VERSION" ]; then
  err "CHANGELOG.md must contain at least one versioned section (e.g. ## [0.1.0] - date)"
  exit 1
fi

# --- 3. Sync: VERSION must match first version in CHANGELOG ---
if [ "$VERSION_CONTENT" != "$CHANGELOG_VERSION" ]; then
  err "VERSION ($VERSION_CONTENT) does not match the first version in CHANGELOG.md ($CHANGELOG_VERSION)"
  exit 1
fi

# --- 4. Optional: when code changed, require VERSION or CHANGELOG updated in PR ---
if [ "${CHECK_UPDATED:-0}" = "1" ] && [ -n "${BASE_REF:-}" ]; then
  BASE_SHA=$(git merge-base "origin/$BASE_REF" HEAD 2>/dev/null || true)
  if [ -n "$BASE_SHA" ]; then
    CHANGED_FILES=$(git diff --name-only "$BASE_SHA"...HEAD 2>/dev/null || true)
    PACKAGE_CHANGED=0
    VERSION_OR_CHANGELOG_CHANGED=0
    echo "$CHANGED_FILES" | grep -qE '^src/' && PACKAGE_CHANGED=1 || true
    echo "$CHANGED_FILES" | grep -qx 'composer.json' && PACKAGE_CHANGED=1 || true
    echo "$CHANGED_FILES" | grep -qx 'VERSION' && VERSION_OR_CHANGELOG_CHANGED=1 || true
    echo "$CHANGED_FILES" | grep -qx 'CHANGELOG.md' && VERSION_OR_CHANGELOG_CHANGED=1 || true
    if [ "$PACKAGE_CHANGED" = 1 ] && [ "$VERSION_OR_CHANGELOG_CHANGED" = 0 ]; then
      err "This PR changes src/ or composer.json but neither VERSION nor CHANGELOG.md was updated. Please bump VERSION and/or update CHANGELOG.md."
      exit 1
    fi
  fi
fi

echo "VERSION and CHANGELOG.md are valid and in sync ($VERSION_CONTENT)."
