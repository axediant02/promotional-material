# Skills Guide

This document lists the custom Codex skills currently used around this project and how to invoke them quickly.

## Command format

Preferred command style:

```text
Use $skill-name to do the task
```

Explicit path style also works when needed:

```text
[$skill-name](C:\Users\Production\.codex\skills\skill-name\SKILL.md) your request here
```

## `git-commit`

### Name
`git-commit`

### Command

```text
Use $git-commit to review my changes and commit them.
```

### Example

```text
Use $git-commit to split my current changes into logical commits and push them.
```

### Use case
- review current git changes before committing
- split unrelated work into separate commits
- draft Conventional Commit messages
- stage only the correct files
- commit and optionally push safely

## `project-docs-sync`

### Name
`project-docs-sync`

### Command

```text
Use $project-docs-sync to sync the docs with the current implementation.
```

### Example

```text
Use $project-docs-sync to update README, AGENTS, and docs after backend schema changes.
```

### Use case
- check for documentation drift
- sync root `README.md`, root `AGENTS.md`, and folder-specific docs
- keep current behavior separate from planned behavior
- update project references after backend or frontend changes

## `bug-tracer`

### Name
`bug-tracer`

### Command

```text
Use $bug-tracer to investigate this error and propose the safest fix.
```

### Example

```text
Use $bug-tracer to trace this failed Laravel request, identify the likely root cause, and suggest fix options before editing code.
```

### Use case
- trace runtime exceptions and stack traces
- inspect failed tests and broken builds
- isolate likely root causes from logs, contracts, schema, and code paths
- propose safe fix options before implementation
- distinguish code bugs from migration drift, permission drift, config issues, or stale docs

## `brainstorm-assistant`

### Name
`brainstorm-assistant`

### Command

```text
Use $brainstorm-assistant to help me decide what to do next.
```

### Example

```text
Use $brainstorm-assistant to compare my options for handling this issue, weigh the tradeoffs, and recommend the best next step.
```

### Use case
- brainstorm ideas when the next move is unclear
- compare feature, workflow, or architecture options
- think through issue handling and risk tradeoffs
- decide whether to build, defer, refactor, or optimize
- choose practical conventions or practices for the project
- turn uncertainty into a recommended next step with concrete actions

## Notes

- The `$skill-name` format is the easiest and recommended way to invoke a skill.
- Skills do not run in the background by themselves.
- A skill changes how Codex handles the task after you explicitly invoke it.
