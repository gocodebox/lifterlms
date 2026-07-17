LifterLMS CLI
=============

[![Test PHPUnit][img-gh-testing]][link-gh-testing]
[![GitHub Coding Standards Workflow Status][img-gh-cs]][link-gh-cs]
[![Code Climate maintainability][img-cc-maintainability]][link-cc]
[![Code Climate test coverage][img-cc-coverage]][link-cc-coverage]

---

WP-CLI commands for [LifterLMS](https://github.com/gocodebox/lifterlms). Manage courses, memberships, enrollments, students, and more from the command line.

This is a feature plugin which will be included in the LifterLMS core plugin automatically.

---

## Installation

Install as a WP-CLI package:

```bash
wp package install gocodebox/lifterlms-cli
```

Or clone into your `wp-content/plugins` directory:

```bash
cd wp-content/plugins
git clone https://github.com/gocodebox/lifterlms-cli.git
```

**Requirements:**
- PHP 7.4+
- WordPress 5.0+
- [LifterLMS](https://lifterlms.com) 5.0+
- [WP-CLI](https://wp-cli.org/) 2.x

## Quick Start

```bash
# List all courses
wp llms course list

# Get a specific course
wp llms course get 42

# Create a course
wp llms course create --title="Introduction to Python" --status=draft

# Get course structure (sections + lessons)
wp llms course content 42

# List enrolled students
wp llms course enrollments 42

# Enroll a student in a course
wp llms students-enrollments create --student_id=5 --post_id=42

# Check student progress
wp llms students-progress get 5 --post_id=42
```

## Commands

### Resource Commands

All resource commands support `list`, `get`, `create`, `update`, `delete`, `diff`, `edit`, and `generate` subcommands.

| Command | Description |
|---------|-------------|
| `wp llms course` | Manage courses |
| `wp llms section` | Manage sections |
| `wp llms lesson` | Manage lessons |
| `wp llms membership` | Manage memberships |
| `wp llms access-plan` | Manage access plans (pricing) |
| `wp llms student` | Manage students |
| `wp llms instructor` | Manage instructors |
| `wp llms students-enrollments` | Manage student enrollments |
| `wp llms students-progress` | Manage student progress |
| `wp llms api-key` | Manage REST API keys |

### Course Sub-Resource Commands

| Command | Description |
|---------|-------------|
| `wp llms course content <id>` | Get course structure (sections + lessons) |
| `wp llms course enrollments <id>` | List students enrolled in a course |

### Management Commands

| Command | Description |
|---------|-------------|
| `wp llms addon` | Manage LifterLMS add-ons (requires LifterLMS Helper) |
| `wp llms license` | Manage add-on licenses (requires LifterLMS Helper) |
| `wp llms version` | Display LifterLMS version |

## Output Formats

All commands support multiple output formats via `--format`:

```bash
# Default table format
wp llms course list

# JSON (recommended for scripts and AI agents)
wp llms course list --format=json

# CSV
wp llms course list --format=csv

# Just IDs
wp llms course list --format=ids

# YAML
wp llms course list --format=yaml

# Count
wp llms course list --format=count
```

Limit output to specific fields:

```bash
wp llms course list --fields=id,title,status --format=json
```

Get just the ID after creating/updating:

```bash
wp llms course create --title="My Course" --porcelain
# Returns: 42
```

## Using with AI Agents

The LifterLMS CLI works with AI coding assistants like Claude Code, Cursor, and Codex. See the [AI Agent Guide](docs/ai-agents.md) for detailed patterns and examples.

Key tips:
- Always use `--format=json` for structured, parseable output
- Use `--fields` to reduce response size
- Use `--porcelain` on create/update to get just the new ID
- Chain commands with pipes: `wp llms course list --format=ids | xargs -I{} wp llms course get {} --format=json`

## Remote Sites

Use [WP-CLI aliases](https://make.wordpress.org/cli/handbook/guides/running-commands-remotely/) to manage remote sites:

```yaml
# ~/.wp-cli/config.yml
@staging:
  ssh: user@staging.example.com/var/www/html
@production:
  ssh: user@example.com/var/www/html
```

```bash
wp @staging llms course list
wp @production llms student list --format=json
```

## Documentation

Full command reference is available at [developer.lifterlms.com/cli/commands](https://developer.lifterlms.com/cli/commands/) and in the [docs/](./docs) directory.

## Contributing

Please follow the contribution guidelines put forth by the [LifterLMS core](https://github.com/gocodebox/lifterlms/blob/trunk/.github/CONTRIBUTING.md).



[img-cc-coverage]:https://img.shields.io/codeclimate/coverage/gocodebox/lifterlms-cli?style=for-the-badge&logo=code-climate
[img-cc-maintainability]:https://img.shields.io/codeclimate/maintainability/gocodebox/lifterlms-cli?logo=code-climate&style=for-the-badge
[img-gh-testing]:https://img.shields.io/github/workflow/status/gocodebox/lifterlms-cli/Test%20PHPUnit?label=tests&logo=github&style=for-the-badge
[img-gh-cs]:https://img.shields.io/github/workflow/status/gocodebox/lifterlms-cli/Coding%20Standards?label=phpcs&logo=github&style=for-the-badge

[link-cc]: https://codeclimate.com/github/gocodebox/lifterlms-cli "Maintainability reports on Code Climate"
[link-cc-coverage]: https://codeclimate.com/github/gocodebox/lifterlms-cli/coverage "Code coverage reports on Code Climate"
[link-gh-testing]: https://github.com/gocodebox/lifterlms-cli/actions/workflows/test-phpunit.yml "Testing workflow on GitHub Actions"
[link-gh-cs]: https://github.com/gocodebox/lifterlms-cli/actions/workflows/check-cs.yml "Coding Standards workflow on GitHub Actions"
