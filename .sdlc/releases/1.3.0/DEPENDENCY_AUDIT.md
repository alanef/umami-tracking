# Dependency Audit: 1.3.0

**Date:** 2026-10-25T14:45:34Z
**Commit:** f6e678d

| Tree | Policy | Exit | Result |
|---|---|---|---|
| plugin composer (no dev) | block | 0 | none shipped |
| root composer (tooling) | warn | 0 | clean |
| npm (dev env / build) | warn | 1 | advisories |

## plugin composer (no dev)
```
umami-tracking/composer.json absent: the plugin ships no Composer dependencies.
```
## root composer
```
No security vulnerability advisories found.
```
## npm
```
### repository root (exit 1)
fix available via `npm audit fix --force`
Will install @wordpress/env@11.17.0, which is a breaking change
node_modules/@simple-git/argv-parser
  simple-git  <=4.0.1
  Depends on vulnerable versions of @simple-git/argv-parser
  node_modules/simple-git
    @wordpress/env  1.0.1 - 11.17.0
    Depends on vulnerable versions of extract-zip
    Depends on vulnerable versions of simple-git
    node_modules/@wordpress/env

extract-zip  *
Severity: high
extract-zip unvalidated symlink path traversal - https://github.com/advisories/GHSA-jmr9-qjv8-65gv
extract-zip allows arbitrary file writes through symlink archive entries - https://github.com/advisories/GHSA-7pqw-9j4j-h8q3
fix available via `npm audit fix --force`
Will install @wordpress/env@11.17.0, which is a breaking change
node_modules/extract-zip

qs  2.2.5 - 6.15.3
Severity: moderate
qs array-limit bypass via bracket-key comma parsing - https://github.com/advisories/GHSA-x5fp-wj9c-mxmx
qs: Denial of Service via Attacker Controlled isBuffer - https://github.com/advisories/GHSA-4mjr-xmp4-gh2g
fix available via `npm audit fix`
node_modules/qs
  express  4.22.2
  Depends on vulnerable versions of qs
  node_modules/express
    @wp-playground/cli  >=3.1.38
    Depends on vulnerable versions of express
    node_modules/@wp-playground/cli


7 vulnerabilities (3 moderate, 2 high, 2 critical)

To address issues that do not require attention, run:
  npm audit fix

To address all issues (including breaking changes), run:
  npm audit fix --force

```
