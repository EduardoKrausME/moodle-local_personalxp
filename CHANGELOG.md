# Changelog

## Unreleased - 2026-10-24

- Updated the MCP banner Mustache header, PHP file headers and language-string ordering to align with Moodle plugin validation conventions.
- Replaced legacy `core_text` string helpers with native PHP functions in adaptive activity XP text handling.

## 1.3.8 - 2026-10-19

- Added a dismissible MCP integration notice for administrators, with Moodle language strings and a Mustache template.
- Refined the notice implementation and PHPDocs to satisfy Moodle coding-style checks.

## 1.3.7 - 2026-10-15

- Followed up on backup/restore validation and Moodle coding-style fixes.
- Improved release packaging documentation and checks for a correctly rooted installable plugin ZIP.

## 1.3.6 - 2026-10-15

- Added course and activity backup/restore support for Personal XP, including user-scoped award history and activity XP settings.
- Remapped restored course, user, activity, forum and quiz references while preserving XP award idempotency.
- Added backup/restore regression tests and documentation for restoring XP totals.

## 1.3.5 - 2026-10-12

- Added optional MCP access to personal course XP, XP history, XP settings and confirmed settings updates, with Moodle capabilities and per-user visibility enforced.
- Hardened automatic MCP provider discovery and added integration tests.
- Updated Moodle 5.3 compatibility and database CI coverage.

## 1.3.2 - 2026-10-07

- Marked the plugin as stable and improved CI dependency setup.

## 1.3.1 - 2026-10-04

- Exposed per-activity XP display state to Moodle theme integrations.
- Updated the Personal XP integration API version and added XP history querying.

## 1.2.0 - 2026-09-30

- Added required dependency on \`local_ai_bridge\` 2026093001.
- Added adaptive XP per course module based on estimated effort and difficulty.
- Added deterministic local fallback and asynchronous AI refinement using purpose \`personalxp-assess-activity\`.
- Reassess Quiz XP when questions are added, removed or switched to another version.
- Added configurable XP/minute and per-activity minimum/maximum limits.

## 1.0.0 - 2026-09-29

- Initial release.
- Personal XP dashboard and course report.
- Activity completion, forum participation, quiz submission and course completion awards.
- Duplicate-award protection and forum daily cap.
- Configurable levels.
- Privacy API implementation.
- English and Brazilian Portuguese strings.
