#!/usr/bin/env bash
set -euo pipefail

# Python venv for pre-commit / MkDocs
python3 -m venv /home/vscode/.venv
/home/vscode/.venv/bin/pip install -r requirements.txt \
    || echo '[setup] WARNING: pip install failed — MkDocs and other Python tools may be missing.'

# Register pre-commit git hooks (pre-commit + commit-msg stages)
# pre-commit itself comes from the devcontainer feature (system Python), not the venv above.
pre-commit install --hook-type pre-commit --hook-type commit-msg

# Configure Git SSH signing when key is mapped via docker-compose.override.yml
if [ -f /home/vscode/.ssh/id_ed25519.pub ]; then
    git config --global gpg.format ssh
    git config --global user.signingkey /home/vscode/.ssh/id_ed25519.pub
    git config --global commit.gpgsign true
    echo '[setup] Git SSH commit signing configured.'
fi
