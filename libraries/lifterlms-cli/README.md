LifterLMS CLI
=============

WP-CLI commands for [LifterLMS](https://github.com/gocodebox/lifterlms). Manage courses, memberships, enrollments, students, and more from the command line.

This directory is part of the LifterLMS core repository and ships with LifterLMS. It is not a standalone plugin and it is not installed with `wp package install`. The old [gocodebox/lifterlms-cli](https://github.com/gocodebox/lifterlms-cli) repository is archived.

Commands load only when WP-CLI is running. Define `LLMS_CLI_DISABLE` as true before LifterLMS loads to skip loading them.

**Requirements:** PHP 7.4+, WordPress 5.9+, and [WP-CLI](https://wp-cli.org/) 2.x, same as LifterLMS core.

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

Follow the [LifterLMS core contribution guidelines](../../.github/CONTRIBUTING.md). Changelog entries go in the core `.changelogs/` directory.
