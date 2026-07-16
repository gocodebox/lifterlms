# LifterLMS for AI Agents

LifterLMS provides two interfaces for AI coding assistants to manage courses, memberships, enrollments, and student data programmatically.

## CLI (WP-CLI)

The [LifterLMS CLI](https://github.com/gocodebox/lifterlms-cli) is a WP-CLI extension included in LifterLMS core builds. It provides full CRUD access to all LifterLMS resources from the command line.

**Best for:** AI agents with shell access — Claude Code, Codex, Cursor terminal.

### Quick Start

```bash
# List courses as JSON
wp llms course list --format=json

# Get course structure (sections + lessons)
wp llms course content 42 --format=json

# Create a course
wp llms course create --title="My Course" --status=draft --porcelain

# Enroll a student
wp llms students-enrollments create --student_id=5 --post_id=42

# Check progress
wp llms students-progress get 5 --post_id=42 --format=json
```

### Available Resources

| Command | Description |
|---------|-------------|
| `wp llms course` | Courses (list, get, create, update, delete, content, enrollments) |
| `wp llms section` | Sections |
| `wp llms lesson` | Lessons |
| `wp llms membership` | Memberships |
| `wp llms access-plan` | Access plans (pricing) |
| `wp llms student` | Students |
| `wp llms instructor` | Instructors |
| `wp llms students-enrollments` | Enrollments |
| `wp llms students-progress` | Student progress |
| `wp llms api-key` | REST API keys |
| `wp llms addon` | Add-on management |

### Tips for AI Agents

- **Always use `--format=json`** for parseable output
- **Use `--fields=id,title,status`** to limit response size
- **Use `--porcelain`** on create/update to get just the new ID for chaining
- See the full [AI agent guide](https://github.com/gocodebox/lifterlms-cli/blob/trunk/docs/ai-agents.md) in the CLI repo

## MCP Server

The [LifterLMS MCP Server](https://github.com/gocodebox/lifterlms-mcp) is a standalone Node.js server that connects AI assistants to LifterLMS via the REST API using the [Model Context Protocol](https://modelcontextprotocol.io).

**Best for:** AI clients without shell access — Claude Desktop, ChatGPT, remote setups.

### Quick Start

Add to your MCP client config (e.g. Claude Desktop):

```json
{
  "mcpServers": {
    "lifterlms": {
      "command": "npx",
      "args": ["-y", "@gocodebox/lifterlms-mcp"],
      "env": {
        "WP_URL": "https://your-site.com",
        "WP_APP_USER": "admin",
        "WP_APP_PASSWORD": "xxxx xxxx xxxx xxxx xxxx xxxx"
      }
    }
  }
}
```

Requires a WordPress Application Password (Settings → Application Passwords).

### Available Tools

36 tools covering courses, sections, lessons, memberships, access plans, students, enrollments, and progress. Plus 3 MCP resources for site info and catalog browsing.

See the full [MCP server documentation](https://github.com/gocodebox/lifterlms-mcp#readme).

## REST API

Both the CLI and MCP server are built on the LifterLMS REST API (`/wp-json/llms/v1/`). If you need direct HTTP access, the REST API is available with API key or Application Password authentication.

Documentation: [developer.lifterlms.com](https://developer.lifterlms.com/)

## When to Use What

| Scenario | Use |
|----------|-----|
| Shell access to the WordPress server | CLI (`wp llms`) |
| Claude Code / Codex / Cursor terminal | CLI |
| Claude Desktop / ChatGPT | MCP Server |
| No SSH, only HTTP access | MCP Server or REST API |
| CI/CD pipelines and scripts | CLI |
| Custom integrations | REST API |
