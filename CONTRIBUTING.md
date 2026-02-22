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

- **Requirements:** PHP 8.4 or later, Composer 2.x. Docker and Dev Container are
  supported for development.
- **Setup:**
  1. Clone the repository.
  2. Run `composer install`.
  3. Run `make install` to install pre-commit hooks and ensure dependencies
     are ready.

## Running checks

Before pushing, run the full check suite from the repository root:

```bash
make all
```

This runs:

- `make format-check` — PSR-12 style check (PHP-CS-Fixer)
- `make lint` — PHP_CodeSniffer (PSR-12)
- `make static-analyse` — PHPStan (level max)
- `make test` — PHPUnit

To fix code style automatically:

```bash
make format
```

Individual targets: `make format`, `make format-check`, `make lint`,
`make static-analyse`, `make test`. See the [Makefile](Makefile) for details.

## Code and style

- Follow the project [Coding standards](docs/coding-standards.md). They align
  with DDD, Clean Architecture, and this package’s patterns.
- Code must be PSR-12. Use `make format` and `make lint` so your changes pass
  the automated checks.
- When adding or changing patterns, update the [BankAccount fixture](tests/Fixtures/BankAccount/)
  and the [documentation](docs/) as needed (see the project rules).

## Pull request process

1. Branch from `main`. One logical change per pull request.
2. Ensure `make all` passes locally (CI runs the same checks).
3. Write clear commit messages and a short PR description.
4. For new patterns or larger changes, consider the [BankAccount fixture](tests/Fixtures/BankAccount/)
  and [component reference](docs/component-reference.md) as references.

Maintainers will review and may request changes. Once approved, your PR can be
merged.

## Security

**Do not open public issues for security vulnerabilities.**

Please report security issues privately as described in our
[Security Policy](.github/SECURITY.md) (GitHub Security Advisories or email to
the maintainer).

## License

By contributing, you agree that your contributions will be licensed under the
[MIT License](LICENSE).
