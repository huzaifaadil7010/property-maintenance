# Project Context

Before implementing or changing any business feature, read the relevant documentation from the `docs` directory.

Use `docs/04-database-structure-mvp.md` as the source of truth for database structure, relationships, tenancy, roles, and enums.

Use the related flow document for the feature being changed:

* `docs/01-owner-manager-mvp-flow.md`
* `docs/02-resident-mvp-flow.md`
* `docs/03-technician-mvp-flow.md`

Use for db structure
* `docs/04-database-structure-mvp.md`

Keep all implementations within the documented MVP scope. Do not introduce undocumented product features or architectural changes without being explicitly asked.

## Tests

Do not create, modify, or add test cases unless the prompt explicitly asks for tests.

## Static Analysis and PHPDoc

Do not install, configure, or run PHPStan or Larastan unless explicitly requested.

Do not add PHPDoc blocks, array-shape annotations, generic types, or other comments solely for static analysis. Prefer native PHP types and clear code.

Do not remove or modify existing PHPDoc unless required by the requested change.


