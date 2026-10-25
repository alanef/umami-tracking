# Release 1.3.0

**Date:** 2026-10-25
**Previous:** v1.2.2

## Highlights

Two fixes:
- A site with no tracker URL no longer loads the placeholder `analytics.example.com` and silently records nothing. fullworksplugins.com lost tracking from 24 Aug to 9 Oct this way.
- The plugin now updates itself from its GitHub releases.

## What's New

### Fixes
- No placeholder default for the tracker URL. Nothing is output until the website ID and a real tracker URL are both set (PLUGINS_SMALL-3).
- A saved URL on an example domain (`example.com`, `.net`, `.org`, any subdomain) counts as unset. The settings field shows it empty, with the example only as a placeholder (PLUGINS_SMALL-3).

### Added
- Admin notice for administrators: "Umami Tracking is not recording any visits", until the plugin is configured (PLUGINS_SMALL-3).
- Self-update from GitHub releases through core's `Update URI` mechanism (PLUGINS_SMALL-1).

### Removed / Changed
- The `GitHub Plugin URI` header (Git Updater) is removed, so only one updater runs (PLUGINS_SMALL-1).
- `Requires PHP: 7.4` is declared (PLUGINS_SMALL-1).

## Breaking Changes

None for configured sites. A site still on the placeholder URL stops loading a script that never worked, and its administrators now see the notice.

## Migration Guide

1.2.2 has no updater, so it will not be offered 1.3.0. Each live site needs one manual update to `umami-tracking-1.3.0.zip`, through HOSTING DevOps. From 1.3.0 on, sites update themselves.

## Technical Details

### Issues Included
| Issue | Title | Type |
|-------|-------|------|
| PLUGINS_SMALL-1 | Boilerplate self-updater for Umami Tracking | Feature |
| PLUGINS_SMALL-3 | Placeholder tracker URL means nothing is tracked | Fix |

### Commits
- ce5ff99: wp-env config fix (dev only)
- 26ee29b: PLUGINS_SMALL-1, boilerplate tooling sync, GitHub updater, Requires PHP, Git Updater header removed
- f6e678d: PLUGINS_SMALL-3, no tracker output without a real tracker URL, admin notice, tests
- Release 1.3.0 (version bump, regenerated .pot)
