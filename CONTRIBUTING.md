# Contributing

Thank you for helping improve Property Maintenance.

## Development setup

Follow the [README setup instructions](README.md#quick-start), then create a focused branch from the repository's default branch:

```bash
git switch -c feature/short-description
```

Keep changes scoped to one concern and follow the patterns already used by neighboring Laravel, Inertia, and React files.

## Before opening a pull request

Run the checks relevant to your change:

```bash
composer lint:check
npm run format:check
npm run lint:check
npm run types:check
php artisan test
npm run build
```

For UI changes, verify the affected flow in a browser at desktop and mobile widths. Include screenshots or a short recording when the visual result is important.

For database changes, explain the migration and rollback impact. Never include real credentials, `.env` files, local databases, uploaded customer data, or production exports.

## Pull requests

A useful pull request includes:

- A clear problem statement and summary of the solution
- The checks that were run
- Any configuration, migration, queue, or webhook changes
- Screenshots for user-facing changes
- A link to the related issue, when one exists

By contributing, you agree that your contribution is licensed under the repository's MIT License.
