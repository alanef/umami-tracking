<!-- Managed by wordpress-plugin-boilerplate/tooling (deploy-live.github.md) - fix there first, then run bin/sync-tooling.sh -->
# Deploy live: Umami Tracking

Run by `/tk-deploy live`. Releases the version already set in the plugin header. This
plugin has no `Type:` header in `umami-tracking/readme.txt`, so it is released on GitHub
only: one tag here runs `release.yml`, which re-runs the checks and creates the GitHub
release with `umami-tracking-$V.zip` attached. Sites update themselves from that release
through `umami-tracking/includes/class-github-updater.php`.

Stop at the first failure and report it. Never retry a tag push, never force
anything, never delete a tag to "try again". Use absolute paths throughout:
`CDPATH` is set on Alan's machines and sends a relative `cd` somewhere else.

```bash
P=$(git rev-parse --show-toplevel)
GIT_TERMINAL_PROMPT=0 git -C $P fetch origin --tags
V=$(grep -m1 -oP '^[ \t]*\*?[ \t]*Version:[ \t]*\K\S+' $P/umami-tracking/umami-tracking.php)
TAG=v$V          # this repo's tag style (pin "tag_prefix" in .tooling.json)
PREV=$(git -C $P tag -l | grep -E '^v?[0-9]+(\.[0-9]+)+$' | awk '{t=$0; sub(/^v/,"",t); print t" "$0}' | sort -V | tail -1 | cut -d' ' -f2)
KEY=$(grep -oP '^YOUTRACK_PROJECT=["'\'']?\K[A-Za-z0-9_]+' $P/.env.youtrack 2>/dev/null)
KEY=${KEY:-}   # empty: YouTrack step is skipped with a note
KB=""             # KB changelog page: the changelog_url pin, else the readme's link
KB=${KB:-$(grep -m1 -oE 'https://fullworksplugins\.com/docs/[^ )"<>]*change-log[^ )"<>]*' $P/umami-tracking/readme.txt)}
```

Check `git -C $P remote get-url origin` names `alanef/umami-tracking` before going on.

## Preconditions

All must hold, otherwise stop:

- On `master` and level with `origin/master`
  (`git -C $P rev-parse HEAD` = `git -C $P rev-parse origin/master`).
- No tracked changes: `git -C $P status --porcelain --untracked-files=no` is empty.
- Version agreement everywhere, no prerelease suffix, changelog heading in place:
  `$P/scripts/release/bump-version.sh --check $V` exits 0.
- Tag absent locally and on origin, in either style (release.yml fires on both):
  `git -C $P tag -l "$V" "v$V"` and
  `GIT_TERMINAL_PROMPT=0 git -C $P ls-remote --tags origin "refs/tags/$V" "refs/tags/v$V"` are empty.
- `$P/.sdlc/releases/$V/RELEASE_NOTES.md` exists (from `/tk-release $V`).
- `$P/.sdlc/releases/$V/TESTS_PASSED.md` exists, and its commit
  `C=$(grep -oP '^\*\*Commit:\*\* \K[0-9a-f]+' $P/.sdlc/releases/$V/TESTS_PASSED.md)`
  is an ancestor of HEAD (`git -C $P merge-base --is-ancestor $C HEAD`) with no plugin
  code changed since
  (`git -C $P diff --quiet $C HEAD -- umami-tracking/ ':!umami-tracking/changelog.txt'`).
  Otherwise re-run `$P/scripts/release/run-tests-and-mark.sh $V`. If the marker shows
  PHPUnit `SKIPPED`, say so to the user when asking for confirmation.
- `$P/.sdlc/releases/$V/DEPENDENCY_AUDIT.md` exists (from `scripts/release/check-dependencies.sh`).
- **KB changelog page.** If `$KB` is empty the plugin has no KB changelog page; note
  "no KB changelog page" and go on. If `$KB` is set, `$P/.env.wordpress` must exist and set
  both `WP_POST_ID` and `CHANGELOG_FILE`:
  `grep -qE '^(export )?WP_POST_ID=.+' $P/.env.wordpress && grep -qE '^(export )?CHANGELOG_FILE=.+' $P/.env.wordpress`.
  If not, **stop** and tell the user, on its own line:
  `CHANGELOG PAGE WILL NOT BE UPDATED: no .env.wordpress (page: $KB)` (or name the missing
  key). The file is gitignored and is lost when a repo is re-cloned or moved; a release
  without it ships with a stale public changelog. Continue only on an explicit yes that
  accepts this, and remember it for the final report.
- The latest `checks.yml` run on `master` is for HEAD and succeeded:
  `gh run list -R alanef/umami-tracking --workflow checks.yml --branch master --limit 1 --json headSha,status,conclusion`
  shows `headSha` = HEAD, `completed`, `success`.

Then show the user: `$PREV..HEAD` (`git -C $P log --oneline $PREV..HEAD`; no tags yet
means `$PREV` is empty, so show `git -C $P log --oneline -20` and say it is a first release), the issue ids
in range, the `= $V =` section of `umami-tracking/changelog.txt`, and exactly what the next
step will publish. **Ask for explicit confirmation before tagging.** Anything other than a
clear yes means stop.

## Tag and push

```bash
git -C $P tag $TAG && GIT_TERMINAL_PROMPT=0 git -C $P push origin $TAG
```

If the push fails, stop and report: the tag then exists only locally. Do not delete it,
retry or force; tell the user.

## Watch release.yml

```bash
gh run list -R alanef/umami-tracking --workflow release.yml --limit 5 --json databaseId,headBranch,status
gh run watch <id> -R alanef/umami-tracking --exit-status     # the run whose headBranch is $TAG
```

Run the watch in the background and wait for completion. Every job must succeed. On
failure show `gh run view <id> -R alanef/umami-tracking --log-failed` and stop.

## Confirm the release asset

The self-updater builds `releases/download/<tag>/umami-tracking-<version>.zip`, so that
exact asset name must exist or every site's update fails:

```bash
gh release view $TAG -R alanef/umami-tracking --json assets,isLatest -q '.isLatest, .assets[].name'
# must print true and list umami-tracking-$V.zip
```

Sites pick the update up on their next check: the updater caches the latest release for
6 hours (1 hour after a failure). "Check again" on Dashboard -> Updates refreshes it at once.

## Publish the changelog

`update-changelog.sh` is on PATH from dev_scripts. It reads `.env.wordpress` in the
project root (gitignored: site, credentials, `WP_POST_ID`, `CHANGELOG_FILE`) and publishes
`umami-tracking/changelog.txt` to the KB page `$KB`.

- `$KB` empty: no KB changelog page. Note "no KB changelog page" and go on; nothing to warn.
- `$KB` set and `.env.wordpress` complete (as checked in the preconditions):

  ```bash
  (cd $P && update-changelog.sh); echo "exit $?"
  curl -s "$KB" | grep -c "Version $V"     # must be at least 1
  ```

- `$KB` set but the user accepted, at the preconditions, releasing without `.env.wordpress`:
  do not run it.

If the publish was skipped, exited non-zero, or the page does not show `Version $V`, the
final report MUST carry a separate, prominent line, never folded into a summary:

```
CHANGELOG PAGE NOT UPDATED: <reason: no .env.wordpress / missing WP_POST_ID / update-changelog.sh exit N / page does not show Version $V>, page $KB
```

The release has already shipped, so carry on to YouTrack; do not re-tag or re-run the release.

## YouTrack

If `$KEY` is empty, skip this step and say so: no `.env.youtrack` `YOUTRACK_PROJECT` and no
`youtrack_project` pin in `.tooling.json`.

Issues in the release: the `Issues Included` table in `.sdlc/releases/$V/RELEASE_NOTES.md`,
else ids at the start of commit subjects in range
(`git -C $P log --format=%s $PREV..$TAG | grep -oE "^$KEY-[0-9]+" | sort -u`), not body
mentions.

Decide per issue whether it shipped plugin code: list the paths its commits in range touch,
and check whether any is inside `umami-tracking/`. Decide by path, never by commit subject:

```bash
ID=<issue id>   # \b keeps QPP-6 from matching QPP-61
for c in $(git -C $P log --format=%H --grep="^$ID\b" -E $PREV..$TAG); do
  git -C $P diff-tree --no-commit-id --name-only -r $c
done | grep -q '^umami-tracking/' && echo shipped || echo non-shipping
```

For each issue:

- Stage: `Deployed`, **only if it shipped plugin code**. An issue whose commits in range touch
  only paths outside `umami-tracking/` (tests, tooling, marketing, docs) keeps its Stage as it is.
- Resolution: `Done`, if the issue is not already resolved.
- Fix versions: `$V`, only if that version already exists in the project's Fix versions
  bundle (check the field schema with `get_issue_fields_schema`). If it does not, say so in
  the report and leave the field alone; never create the version.
- Comment, on every issue in range including non-shipping ones: `Released in $V`, with the
  GitHub release link (`https://github.com/alanef/umami-tracking/releases/tag/$TAG`) and, for anything
  user-visible, one line on what changed.

## Finish

Report: tag, release.yml run, the asset confirmed, changelog result, YouTrack updates, and
that sites will see `$V` within 6 hours. Put any `CHANGELOG PAGE NOT UPDATED` line on its own, first.
