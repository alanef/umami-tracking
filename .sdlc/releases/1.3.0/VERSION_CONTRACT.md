# Version Contract: 1.3.0

**Date:** 2026-10-25

## Canonical Source

These are written and read back by `scripts/release/bump-version.sh`, and CI fails when they disagree:
- the plugin header `Version:` in umami-tracking/umami-tracking.php;
- `UMAMI_TRACKING_VERSION`;
- `Stable tag:` in umami-tracking/readme.txt;
- package.json, package-lock.json and the .pot.

## Expected State After Deploy

- master has every version location at `1.3.0`, and `= 1.3.0 =` at the top of changelog.txt.
- Git tag `v1.3.0` is on master's HEAD. release.yml creates the GitHub release (not a prerelease) with `umami-tracking-1.3.0.zip` attached. No `Type:` header, so there is no wp.org or Freemius deploy.
- `https://github.com/alanef/umami-tracking/releases/latest` redirects to `/releases/tag/v1.3.0`. That redirect is what the updater reads.

## Required Deploy Command

```
/tk-deploy live
```

## Post-Deploy

- Manual update of the four 1.2.2 sites (lawncrew, badlywired, fullworksplugins, ozlemsturkishtable) via HOSTING DevOps. 1.2.2 has no updater.
- On each site, enter or check the tracker URL. The admin notice shows wherever it was the placeholder.
