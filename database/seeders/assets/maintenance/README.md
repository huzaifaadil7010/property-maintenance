# Maintenance seeder images

These local fixtures were generated specifically for the Northstar Property
Management demonstration dataset with OpenAI image generation on 2026-09-16.
They are intentionally stored in the repository so database seeding never
depends on an external URL or network connection.

Each maintenance category has one issue-state photograph and one
completion-state photograph. The seeders copy these originals into the
request's `issue-images` and `completion-images` Media Library collections;
the source files remain unchanged for repeatable reseeding.

The images contain no logos, text, watermarks, or identifiable people.
