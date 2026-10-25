# Tests Passed: 1.3.0

**Date:** 2026-10-25T14:46:59Z
**Commit:** 3c013f76d60030524031f5688f6c8fb92a346099
**Tracked changes at run time:** 0

| Gate | Result |
|---|---|
| composer install | pass |
| composer validate --strict | pass |
| composer run check | pass |
| version consistency | pass |
| build (composer run build) | pass |
| Plugin Check | not run locally (no plugin-check script); CI runs it on the built zip |
| PHPUnit (npm test) | pass |

PHPUnit summaries:

    OK (35 tests, 70 assertions)

Full output: tests.log (not committed).
