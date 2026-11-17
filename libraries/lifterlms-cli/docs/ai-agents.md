# Using LifterLMS CLI with AI Agents

This guide covers how to use the LifterLMS CLI effectively with AI coding assistants like Claude Code, Cursor, Codex, and similar tools.

## Why CLI for AI Agents?

AI agents that have shell access (Claude Code, Codex, Cursor terminal) can use the LifterLMS CLI directly — no MCP server or API configuration needed. The CLI runs inside WordPress, so it has full access to all LifterLMS data with proper permission handling.

## Essential Patterns

### Always Use JSON Output

AI agents parse structured data, not ASCII tables. Always pass `--format=json`:

```bash
# Bad: returns an ASCII table that's hard to parse
wp llms course list

# Good: returns structured JSON
wp llms course list --format=json
```

### Limit Fields to Reduce Context Size

LLM context windows are finite. Use `--fields` to request only what you need:

```bash
# All fields (verbose)
wp llms course get 42 --format=json

# Just what you need
wp llms course get 42 --fields=id,title,status --format=json
```

### Use --porcelain for Create/Update Workflows

When creating or updating resources, `--porcelain` returns just the ID — useful for chaining:

```bash
# Create a course and capture its ID
COURSE_ID=$(wp llms course create --title="My Course" --status=draft --porcelain)

# Create a section in that course
SECTION_ID=$(wp llms section create --title="Getting Started" --parent_id=$COURSE_ID --porcelain)

# Create a lesson in that section
wp llms lesson create --title="Welcome" --parent_id=$SECTION_ID --status=publish --porcelain
```

### Get Course Structure in One Call

Use `wp llms course content` to see the full outline:

```bash
wp llms course content 42 --format=json
```

Returns sections with their lessons, ordered by position. This is faster than listing sections and lessons separately.

### Check Enrollment Status

```bash
# How many students in a course?
wp llms course enrollments 42 --format=count

# Who's enrolled?
wp llms course enrollments 42 --format=json

# Is student 5 enrolled in course 42?
wp llms students-enrollments list --student_id=5 --post_id=42 --format=json
```

## Common Workflows

### Create a Complete Course

```bash
# 1. Create the course
COURSE_ID=$(wp llms course create --title="Photography Basics" --status=draft --porcelain)

# 2. Create sections
S1=$(wp llms section create --title="Camera Fundamentals" --parent_id=$COURSE_ID --order=1 --porcelain)
S2=$(wp llms section create --title="Composition" --parent_id=$COURSE_ID --order=2 --porcelain)

# 3. Create lessons
wp llms lesson create --title="Aperture & Shutter Speed" --parent_id=$S1 --order=1 --status=publish --porcelain
wp llms lesson create --title="ISO & Exposure" --parent_id=$S1 --order=2 --status=publish --porcelain
wp llms lesson create --title="Rule of Thirds" --parent_id=$S2 --order=1 --status=publish --porcelain
wp llms lesson create --title="Leading Lines" --parent_id=$S2 --order=2 --status=publish --porcelain

# 4. Create an access plan
wp llms access-plan create --title="Full Access" --post_id=$COURSE_ID --price=49 --frequency=0

# 5. Publish
wp llms course update $COURSE_ID --status=publish

# 6. Verify
wp llms course content $COURSE_ID --format=json
```

### Bulk Enrollment

```bash
# Enroll multiple students in a course
for STUDENT_ID in 5 12 28 45; do
  wp llms students-enrollments create --student_id=$STUDENT_ID --post_id=42
done

# Verify enrollments
wp llms course enrollments 42 --format=json
```

### Progress Report

```bash
# Get all enrollments for a course
STUDENTS=$(wp llms course enrollments 42 --fields=student_id --format=json)

# Check each student's progress
wp llms students-progress list --post_id=42 --format=json
```

### Find and Update

```bash
# Find a course by searching
wp llms course list --search="Python" --fields=id,title --format=json

# Update its title
wp llms course update 42 --title="Advanced Python Programming"
```

## Resource Relationships

Understanding how LifterLMS resources relate to each other:

```
Course
├── Sections (ordered, belong to one course)
│   └── Lessons (ordered, belong to one section)
│       └── Quizzes (optional, attached to lessons)
├── Access Plans (pricing, one or more per course)
└── Enrollments (students enrolled in this course)
    └── Progress (per-student, per-course/lesson)

Membership
├── Access Plans (pricing)
├── Auto-enrollment courses (optional)
└── Enrollments (students)
```

Key IDs to track:
- **course_id** / **post_id**: The course or membership ID
- **student_id**: WordPress user ID
- **parent_id**: Parent course (for sections) or parent section (for lessons)

## Error Handling

CLI commands exit with standard exit codes:
- `0` = success
- `1` = error (with message on stderr)

Error messages are human-readable. For AI agents, check the exit code:

```bash
if wp llms course get 99999 --format=json 2>/dev/null; then
  echo "Course exists"
else
  echo "Course not found"
fi
```

## CLI vs MCP Server

LifterLMS has both a CLI and an [MCP server](https://github.com/gocodebox/lifterlms-mcp). When to use which:

| Use CLI when... | Use MCP when... |
|-----------------|-----------------|
| You have shell access to the WordPress server | You're connecting remotely |
| Running Claude Code, Codex, or Cursor locally | Using Claude Desktop or ChatGPT |
| Need to chain commands or write scripts | Want conversational CRUD |
| Working in CI/CD pipelines | Don't have SSH access |

Both tools cover the same LifterLMS resources. The CLI talks to WordPress directly (internal REST requests). The MCP server talks over HTTP (external REST API with Application Passwords).
