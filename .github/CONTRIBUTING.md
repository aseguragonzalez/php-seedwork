# Contributing to PHP SeedWork

Thank you for your interest in contributing. We welcome bug reports, feature
ideas, documentation improvements, and code contributions. By participating,
you agree to uphold our [Code of Conduct](CODE_OF_CONDUCT.md).

## Ways to contribute

- **Report bugs** — Use [GitHub Issues](https://github.com/aseguragonzalez/php-seedwork/issues)
  with the Bug report template.
- **Suggest features** — Open an issue with the Feature request template, or
  start a [Discussion](https://github.com/aseguragonzalez/php-seedwork/discussions).
- **Improve documentation** — Fix typos, clarify wording, or add examples.
- **Submit code** — Fixes, tests, or new features via pull requests.

## Reporting bugs and suggesting features

- **Bugs:** Use the [Bug report](https://github.com/aseguragonzalez/php-seedwork/issues/new?template=bug_report.md)
  template. Include PHP version, package version (e.g. `composer show aseguragonzalez/php-seedwork`),
  and steps to reproduce.
- **Features:** Use the [Feature request](https://github.com/aseguragonzalez/php-seedwork/issues/new?template=feature_request.md)
  template. Describe the use case and, if you have one, a possible solution.

## Development setup

**Requirements:** Docker and the [Dev Containers CLI](https://github.com/devcontainers/cli)
(`devcontainer` command), or VS Code with the
[Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers).

PHP and Composer are **not** required on the host — they run exclusively inside the dev container.

**Setup:**

1. Clone the repository.
2. Start the dev container:

   ```bash
   devcontainer up --workspace-folder .
   ```

3. Install dependencies inside the container:

   ```bash
   devcontainer exec --workspace-folder . make install
   ```

## Running checks

All make targets run inside the dev container:

```bash
devcontainer exec --workspace-folder . make <target>
```

| Command | Description |
|---|---|
| `make install` | Install Composer dependencies and pre-commit hooks |
| `make cs` | Check code style (PHP-CS-Fixer, dry-run) |
| `make cs-fix` | Auto-fix code style |
| `make stan` | Static analysis (PHPStan level max) |
| `make test` | Run PHPUnit with coverage |
| `make check` | Run `cs + stan + test` (no coverage) |
| `make all` | Run `install + cs-fix + check` |

Run `make all` before every commit to ensure everything passes.

## Code and style

- Follow the project [Coding standards](docs/coding-standards.md). They align
  with DDD, Clean Architecture, and this package’s patterns.
- Code must be PSR-12. Use `make format` and `make lint` so your changes pass
  the automated checks.
- When adding or changing patterns, update the [BankAccount example](docs/examples/BankAccount/)
  and the [documentation](docs/) as needed (see the project rules).

## Commit signing

All commits must be **GPG or SSH signed** (verified). This is required to maintain the integrity of the public package history.

### Setting up SSH signing on the host

If you commit directly from the host (outside the dev container), configure git once:

```bash
git config --global gpg.format ssh
git config --global user.signingkey ~/.ssh/id_ed25519.pub
git config --global commit.gpgsign true
```

Replace `id_ed25519.pub` with your actual public key filename if different.

### Setting up SSH signing inside the dev container

The dev container does not include your SSH key by default. To map your local key into the container (bind mount — not a copy):

1. Copy the example override file:

   ```bash
   cp .devcontainer/docker-compose.override.yml.example \
      .devcontainer/docker-compose.override.yml
   ```

2. Edit `.devcontainer/docker-compose.override.yml` if your signing key has a different name or location. The default mounts `~/.ssh` read-only:

   ```yaml
   services:
     app:
       volumes:
         - ~/.ssh:/home/vscode/.ssh:ro
   ```

3. Rebuild the dev container. The `postCreateCommand` detects `/home/vscode/.ssh/id_ed25519` and automatically sets:

   ```
   gpg.format = ssh
   user.signingkey = /home/vscode/.ssh/id_ed25519.pub
   commit.gpgsign = true
   ```

`.devcontainer/docker-compose.override.yml` is listed in `.gitignore` — your local key path is never committed.

## Pull request process

1. Branch from `main`. One logical change per pull request.
2. Ensure `make all` passes locally (CI runs the same checks).
3. Write clear commit messages and a short PR description.
4. For new patterns or larger changes, consider the [BankAccount example](docs/examples/BankAccount/)
  and [component reference](docs/component-reference.md) as references.

Maintainers will review and may request changes. Once approved, your PR can be
merged.

## Security

**Do not open public issues for security vulnerabilities.**

Please report security issues privately as described in our
[Security Policy](SECURITY.md) (GitHub Security Advisories or email to
the maintainer).

## License

By contributing, you agree that your contributions will be licensed under the
[MIT License](LICENSE).
