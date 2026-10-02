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
