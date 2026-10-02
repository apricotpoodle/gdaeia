# Instructions for contributors and agents

## Git branch isolation

Before modifying any file:

1. Inspect the repository with `rtk git status --short --branch`.
2. Never modify `main` directly.
3. When the worktree is clean, create a dedicated branch before editing:
   - `docs/...` for documentation and ADR changes;
   - `feature/...` for feature work;
   - `fix/...` for bug fixes;
   - `refactor/...` for refactoring.
4. When pre-existing changes are present, identify them and preserve them. Do
   not reset, stash, overwrite, or mix them into the new task without an
   explicit decision.
5. Verify the active branch again before the first write.

This rule applies to human contributors and AI agents, including
documentation-only changes. At handoff, report the branch name and the files
changed by the task.

## Command convention

Shell commands must use the `rtk` prefix as described by the repository
instructions.

## Commit messages

Before each commit, verify that the message:

- follows Conventional Commits;
- uses a scope when it adds useful precision;
- contains a description written in French;
- covers only the atomic changes staged for that commit.

Use this format:

```text
<type>(<scope>): <description en français>
```

Usual types are:

- `feat` : fonctionnalité ;
- `fix` : correction ;
- `docs` : documentation ;
- `refactor` : refactorisation ;
- `test` : tests ;
- `chore` : maintenance.

Example:

```text
docs(git): formaliser les règles d’isolation des branches
```

The commit type and structure can be checked automatically, but the French
wording remains a manual review requirement.
