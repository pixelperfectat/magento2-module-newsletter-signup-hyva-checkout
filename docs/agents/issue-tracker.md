# Issue tracker: GitHub

Issues and specs for this repo live as GitHub issues. Use the `gh` CLI for all operations.

## Project conventions (apply to every issue)

These follow the pixelperfectat module issue-tracking rule. They add to the commands below; they never replace them.

- **Milestone = release.** Every new issue goes on the open release milestone: `--milestone 0.1.0` until that
  release is tagged, then the next one. A release is blocked while its milestone has open issues.
- **Labels on every issue:** `feature:newsletter-signup`, plus exactly one change-type label that matches the
  commit keyword and the changelog section: `added`, `changed`, `fixed`, `deprecated`, `removed` or `security`.
  Add `source:internal` (or `source:customer` when the work came from a customer request). Triage-role labels
  (see `triage-labels.md`) come on top.
- **Specs live in the repo, and this feature's spec is in core.** A spec is a file under `docs/specs/<feature-slug>.md`,
  committed through a pull request; its `Feature: <name>` parent issue only links to it. The newsletter-signup
  spec and its parent issue are in `pixelperfectat/magento2-module-newsletter-signup`
  (`docs/specs/newsletter-signup.md`). Do not create a `Feature:` issue or a spec for it here. Tickets for this
  repo are titled `Task: <name>` and are cross-repo sub-issues of that parent (the API form below takes a
  sub-issue from another repo in the same organisation: pass this repo's issue database id to the core repo's
  `issues/<parent>/sub_issues`). A feature that belongs to this module alone gets its spec in this repo's
  `docs/specs/`, the same way.
- **Sub-issues with this `gh` (older than 2.94):** use the API form from **Make an issue a sub-issue of a parent**.
- **Commits** keep the keyword-first subject and end with the issue: `Add subscription status check (#3)`. The
  commit that finishes the issue adds a `Closes #3` trailer.
- **Issue text is published.** This repo is public: never name a shop, a client, a domain, a ticket prefix
  from another tracker, or any real person's data. Describe the product need only.
- **Assignee:** claiming an issue assigns it to `@me`; the maintainer is the only assignee.

## Conventions

- **Create an issue**: `gh issue create --title "..." --body "..."`. Use a heredoc for multi-line bodies.
- **Read an issue**: `gh issue view <number> --json number,title,body,labels,comments`.
- **List issues**: `gh issue list --state open --json number,title,body,labels,comments --jq '[.[] | {number, title, body, labels: [.labels[].name], comments: [.comments[].body]}]'` with appropriate `--label` and `--state` filters.
- **Make an issue a sub-issue of a parent**: `gh issue create --parent <parent> ...`, or `gh issue edit <parent> --add-sub-issue <child>` afterwards (`gh` 2.94+). Older `gh`: `gh api --method POST repos/<owner>/<repo>/issues/<parent>/sub_issues -F sub_issue_id=<child-db-id>` (database id, as in **Blocking** below). Without sub-issues, put `Part of #<parent>` at the top of the child body.
- **Comment on an issue**: `gh issue comment <number> --body "..."`
- **Apply / remove labels**: `gh issue edit <number> --add-label "..."` / `--remove-label "..."`
- **Close**: `gh issue close <number> --comment "..."`

Infer the repo from `git remote -v`; `gh` does this automatically when run inside a clone.

## Pull requests as a triage surface

**PRs as a request surface: no.** _(Set to `yes` if this repo treats external PRs as feature requests; `/triage` reads this flag.)_

When set to `yes`, PRs run through the same labels and states as issues, using the `gh pr` equivalents:

- **Read a PR**: `gh pr view <number> --comments` and `gh pr diff <number>` for the diff.
- **List external PRs for triage**: `gh api --paginate 'repos/{owner}/{repo}/pulls?state=open' --jq '.[] | select(.author_association | IN("OWNER","MEMBER","COLLABORATOR") | not) | {number, title, author: .user.login, author_association, labels: [.labels[].name]}'`.
- **Comment / label / close**: `gh pr comment`, `gh pr edit --add-label`/`--remove-label`, `gh pr close`.

GitHub shares one number space across issues and PRs, so a bare `#42` may be either: resolve with `gh pr view 42` and fall back to `gh issue view 42`.

## When a skill says "publish to the issue tracker"

Create a GitHub issue.

## When a skill says "fetch the relevant ticket"

Read it as in **Read an issue** above.

## Wayfinding operations

Used by `/wayfinder`. The **map** is a single issue with **child** issues as tickets.

- **Map**: a single issue labelled `wayfinder:map`, holding the Notes / Decisions-so-far / Fog body. `gh issue create --label wayfinder:map`.
- **Child ticket**: an issue linked to the map as a GitHub sub-issue (see **Make an issue a sub-issue of a parent**). Where sub-issues aren't enabled, add the child to a task list in the map body and put `Part of #<map>` at the top of the child body. Labels: `wayfinder:<type>` (`research`/`prototype`/`grilling`/`task`). Once claimed, the ticket is assigned to the driving dev.
- **Blocking**: GitHub's **native issue dependencies**, the canonical, UI-visible representation. Add an edge with `gh api --method POST repos/<owner>/<repo>/issues/<child>/dependencies/blocked_by -F issue_id=<blocker-db-id>`, where `<blocker-db-id>` is the blocker's numeric **database id** (`gh api repos/<owner>/<repo>/issues/<n> --jq .id`, _not_ the `#number` or `node_id`). GitHub reports `issue_dependencies_summary.blocked_by` (open blockers only, the live gate). Where dependencies aren't available, fall back to a `Blocked by: #<n>, #<n>` line at the top of the child body. A ticket is unblocked when every blocker is closed.
- **Frontier query**: list the map's open children (`gh issue list --state open`, scoped to the map's sub-issues / task list), drop any with an open blocker (`issue_dependencies_summary.blocked_by > 0`, or an open issue in the `Blocked by` line) or an assignee; first in map order wins.
- **Claim**: `gh issue edit <n> --add-assignee @me`, the session's first write.
- **Resolve**: `gh issue comment <n> --body "<answer>"`, then `gh issue close <n>`, then append a context pointer (gist + link) to the map's Decisions-so-far.
