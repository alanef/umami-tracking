# Change Impact Assessment: 1.3.0

## Risk Level: Low

- The front-end change is one early return: it only stops output when the URL is unset or a placeholder. Configured sites get byte-for-byte the same loader.
- The updater is the boilerplate's managed class, the same one used across the estate, with its own unit tests.
- No database changes. Option keys are unchanged, and the `umami_tracking_url` option is not rewritten.

### Factors Considered
- Number of issues: 2
- Security-sensitive changes: no (output stays escaped, and the notice is gated on `manage_options`)
- API changes: no
- Database changes: no
- Breaking changes: none for configured sites

## Affected Areas
- `includes/class-umami-tracking.php`:
  - `tracker_url()` and `is_configured()`;
  - the early return in `enqueue_tracking_script()`;
  - `render_not_configured_notice()` on `admin_notices`;
  - the setting default and the settings field.
- `umami-tracking.php`: the `Update URI`, `Requires at least` and `Requires PHP` headers; `GitHub Plugin URI` removed; guarded require of `includes/class-github-updater.php`.
- Tooling: CI workflows, release hooks, `/tk-deploy`, wp-env mappings, and tracked lock files.

## Visible changes
- Unconfigured or placeholder sites: no Umami loader on the front end, and an admin notice for administrators.
- The settings field's URL is required, and is empty rather than pre-filled with the example.

## Testing
- `tests/test-tracker-url.php`: 15 cases (it fails on 1.2.2 code). `tests/test-github-updater.php`: updater.
- wp-env front end checked with an unset, a placeholder and a real URL. Dashboard notice and settings field checked.
