# LifterLMS for AI Agents

LifterLMS is a WordPress learning management system plugin for creating, selling, and protecting online courses and membership sites. This file orients AI coding agents working in this repository.

Interface-level documentation for driving a live LifterLMS site (CLI, MCP server, REST API) lives in [`docs/ai-agents.md`](docs/ai-agents.md). This file covers everything else: project shape, contribution workflow, coding rules, and verification discipline.

## Project Shape

LifterLMS is a single WordPress plugin with several bundled packages and a constellation of paid add-ons in separate repositories.

### Core repository layout

```
lifterlms.php                 # WordPress plugin entry point
class-lifterlms.php           # Main plugin class
includes/                     # The bulk of the codebase (PHP source)
  abstracts/                  # Base classes
  achievements/               # Achievement engine
  admin/                      # WordPress admin UI screens, metaboxes
  certificates/               # Certificate engine
  controllers/                # Form / order / quiz / lesson / award controllers
  emails/                     # Transactional email templates
  forms/                      # Form rendering and processing
  functions/                  # Procedural function files (llms-functions-*.php)
  integrations/               # First-party integrations
  interfaces/                 # PHP interfaces
  models/                     # Domain models (course, lesson, student, order, ...)
  notifications/              # Notification engine
  privacy/                    # GDPR exporters and erasers
  processors/                 # Background processors
  schemas/                    # JSON schemas
  shortcodes/                 # Front-end shortcodes
  spam/                       # Anti-spam helpers
  theme-support/              # Twenty Twenty / Astra / OceanWP / etc. compat
  traits/                     # Reusable traits
  widgets/                    # Legacy WP widgets
src/                          # Source files for compiled JS / (S)CSS
  js/, blocks/, scss/         # Compile into assets/ — never commit compiled output
libraries/                    # Vendored packages (populated by composer install)
  lifterlms-rest/             # Installed from gocodebox/lifterlms-rest
packages/                     # Internal monorepo packages (npm workspaces + lerna)
  dev/                        # Maintainer CLI: changelog, release, pot, readme
  brand, components, icons,   # Internal shared code
  scripts, utils, fontawesome
docs/                         # Contributor documentation
  ai-agents.md                # AI integration interfaces (CLI, MCP, REST)
  coding-standards.md         # snake_case, prefixes, file naming, formatting
  documentation-standards.md  # DocBlocks, @since, @deprecated tags
  contributing.md             # Mirror of .github/CONTRIBUTING.md
  installing.md               # Dev environment setup
templates/                    # Front-end PHP templates (override-friendly)
tests/
  phpunit/                    # PHPUnit tests
  e2e/                        # Playwright end-to-end tests
.changelogs/                  # Pending changelog YAML entries (one per PR)
```

### Bundled packages with their own repositories

These ship inside LifterLMS core releases via Composer but are developed in separate **public** repos:

- [`lifterlms-blocks`](https://github.com/gocodebox/lifterlms-blocks) — Gutenberg blocks
- [`lifterlms-cli`](https://github.com/gocodebox/lifterlms-cli) — WP-CLI commands (`wp llms ...`)
- [`lifterlms-rest`](https://github.com/gocodebox/lifterlms-rest) — REST API endpoints (vendored into `libraries/lifterlms-rest/` during development setup)

### The add-on ecosystem

LifterLMS extends through paid add-ons published from **private** repositories under the `gocodebox` GitHub organization. Examples include Stripe, Groups, Private Areas, Events, Advanced Quizzes, Assignments, ConvertKit, Authorize.Net, PDFs, and integrations for Gravity Forms, Ninja Forms, Formidable Forms, WPForms, WooCommerce, and Zapier. The catalog is at https://lifterlms.com/store/.

**Heads up:** add-on repos are private. Agents working on behalf of a LifterLMS team member with `gocodebox` org access can clone, read, and contribute to them. Agents working in the public open-source codebase will not be able to view them. If a hook, filter, or behavior originates in an add-on, the public-facing references are the product page and documentation at https://lifterlms.com/docs/.

## AI Integration Interfaces

Three ways for an agent to drive a live LifterLMS site:

- **CLI** — `wp llms <command>` via the [`lifterlms-cli`](https://github.com/gocodebox/lifterlms-cli) package. For agents with shell access (Claude Code, Cursor, Codex on the server).
- **MCP server** — [`lifterlms-mcp`](https://github.com/gocodebox/lifterlms-mcp), built on the REST API. For agents without shell access (Claude Desktop, ChatGPT).
- **REST API** — `/wp-json/llms/v1/`, documented at https://developer.lifterlms.com/.

Setup, command tables, and the "when to use what" decision matrix live in [`docs/ai-agents.md`](docs/ai-agents.md).

**Directive:** when operating against a live LifterLMS site, prefer `wp llms` (with shell access) or the MCP server / REST API (without) over direct database writes or hand-rolled REST clients. The provided interfaces encode access controls, hooks, and side effects that direct writes bypass.

## Contributing Workflow

Authoritative source: [`.github/CONTRIBUTING.md`](.github/CONTRIBUTING.md). The high-leverage rules:

- **Branch from `dev`.** Cut new branches from the `dev` branch.
- **PR target: `dev`.** Never PR against `trunk`. Trunk holds the released version.
- **Reference issues with auto-link.** `Fixes #1234` or `Closes #1234` in the commit message and PR body where applicable.
- **Add a changelog entry.** From the repo root:

  ```
  npm run dev changelog add -- -i
  ```

  This drops a YAML file in `.changelogs/`. Pick the right significance (`patch`, `minor`, `major`) and type (`added`, `changed`, `fixed`, `deprecated`, `removed`, `dev`, `performance`, `security`). The entry compiles into the next release changelog automatically.
- **Source-only commits.** Edit source files in `src/`. Compiled and minified assets in `assets/` are gitignored build outputs and must not be committed.
- **Tests welcomed, not required.** Critical paths (enrollment, checkout, access plans, core models) deserve PHPUnit coverage; large user-facing flows deserve a Playwright E2E. PRs are not blocked on missing tests, but a change that breaks an existing test must fix or update it.

### Preserving public API signatures

Third-party plugins, add-ons, and customer sites depend on LifterLMS's public functions, methods, action names, filter names, and REST response shapes. Do not reorder parameters, rename arguments, or change return shapes on anything publicly accessible.

- **Append**, do not reorder. New optional parameters with default values are safe.
- **Deprecate**, do not replace silently. If the existing signature cannot accommodate the change, leave the old function in place, mark it `@deprecated` with a pointer to the replacement, and create a new one.

## Coding Standards (short version)

Full rules in [`docs/coding-standards.md`](docs/coding-standards.md). The critical patterns:

- **snake_case everywhere.** Class names, methods, functions, variables, hook names.
- **`LLMS_` class prefix** for core. Add-ons add a sub-prefix (`LLMS_AQ_*` for Advanced Quizzes, `LLMS_SL_*` for Social Learning, etc.).
- **`llms_` function and hook prefix** for core. New hooks use `llms_`; the legacy `lifterlms_` prefix is retained for back-compat only, not for new code.
- **`llms-` CSS class prefix** for front-end markup.
- **File naming.** `class-llms-<name>.php` for classes, `model-llms-<name>.php` for models, `llms-trait-<name>.php` for traits (under `includes/traits/`), `llms-functions-<area>.php` for function files. Lowercase, hyphen-separated. Older files in `includes/models/` use a dot-separated `model.llms.<name>.php` legacy pattern; new files should use the hyphenated form.
- **DocBlocks on everything new.** Summary line, then `@param` and `@return` with full sentences. See [`docs/documentation-standards.md`](docs/documentation-standards.md).

## Verification Discipline

LifterLMS is a mature codebase. Training data on it is often stale. Hook signatures evolve, add-ons get deprecated, REST shapes change between versions.

Rules of the road for AI agents:

1. **Code is canonical.** Before claiming a function, hook, filter, or class exists, grep `includes/` for it. Before describing how it behaves, read the source.
2. **Docs are second.** https://lifterlms.com/docs/ for user docs, https://developer.lifterlms.com/ for developer docs.
3. **Do not fabricate URLs.** Do not construct doc URLs from a name. If a doc page cannot be found through search or a doc index, say so.
4. **Customer assertions are unverified until grepped.** "I'm using the X shortcode" or "the bug is in the Y add-on" is a starting hypothesis, not a fact.
5. **Order status semantics are definitive.** `llms-pending` (initial state, awaiting transaction confirmation) and `llms-failed` (gateway sent an explicit failure event) are not interchangeable. A missing or misconfigured webhook produces `pending`, never `failed`. The valid statuses live in `includes/controllers/class.llms.controller.orders.php`.

## Deprecated Add-Ons

Some LifterLMS add-ons have been sunset. Do not recommend a deprecated add-on as a solution to a problem.

This repo does not yet ship a canonical machine-readable list of deprecated add-ons. To verify whether an add-on is current:

1. Check the official add-on directory at https://lifterlms.com/store/.
2. For LifterLMS team members with `gocodebox` org access: an archived `gocodebox/<addon-slug>` repository signals deprecation.
3. When in doubt, ask in the `#developers` channel of the LifterLMS community Slack at https://lifterlms.com/slack, or open a question issue on this repo.

> **TODO for the LifterLMS team.** Replace this section with an explicit, dated list of deprecated add-ons (slug, last shipped version, recommended replacement if any). A canonical list reduces support load and stops agents from suggesting sunset products to customers.

## Reporting Security Vulnerabilities

**Never** file a security vulnerability as a public GitHub issue.

See [`.github/SECURITY.md`](.github/SECURITY.md) and https://lifterlms.com/security/ for the responsible disclosure process. If an agent discovers what looks like a vulnerability while working in this repo, surface it privately to the LifterLMS core team rather than opening a public issue or PR.

## Where to Look for Things

| Looking for... | Look in... |
|---|---|
| Course, lesson, quiz, membership, order models | `includes/models/` |
| Order, transaction, payment plan controllers | `includes/controllers/` |
| Front-end form rendering and processing | `includes/forms/` |
| Admin UI screens and metaboxes | `includes/admin/` |
| Hooks (actions / filters) | grep `includes/` for `do_action` or `apply_filters` |
| REST endpoints | the [`lifterlms-rest`](https://github.com/gocodebox/lifterlms-rest) repo, or `libraries/lifterlms-rest/` after `composer install` |
| CLI command shapes | the [`lifterlms-cli`](https://github.com/gocodebox/lifterlms-cli) repo |
| Block definitions | the [`lifterlms-blocks`](https://github.com/gocodebox/lifterlms-blocks) repo |
| Sample / fixture data | `sample-data/` |
| Front-end PHP templates | `templates/` |
| Build configuration | `webpack.config.js`, `gulpfile.js/`, `package.json` |
| Test scaffolding | `tests/phpunit/`, `tests/e2e/` |

## Notes for AI Agents

- This file is orientation. Procedural details live in `docs/` and `.github/CONTRIBUTING.md`. Read them.
- When uncertain about a hook, function, or behavior: read the code first, then ask.
- `CLAUDE.md` at the repo root imports this file, so Claude Code reads it on session start. No separate Anthropic-specific maintenance needed.
