# Umami Tracking WordPress Plugin

A WordPress plugin that adds the Umami tracking script to your website.

## Overview

This repository contains the Umami Tracking WordPress plugin.

## Features

- Easy configuration of your Umami Website ID through the WordPress admin.
- Adds the Umami tracking script to the `<head>` of your site.

## Installation

1.  Download the latest release from the [releases page](https://github.com/alanef/umami-tracking/releases).
2.  In your WordPress admin, go to Plugins > Add New > Upload Plugin.
3.  Choose the downloaded zip file and click "Install Now".
4.  Activate the plugin.
5.  Go to Settings > Umami Tracking and enter your Umami Website ID.

## Development

### Prerequisites

-   PHP 7.2 or higher
-   Composer
-   WP-CLI (for building releases)

### Building for Release

To create a distributable zip file of the plugin, run the following command:

```bash
wp dist-archive . --plugin-dirname=umami-tracking --format=zip
```

This will create a `umami-tracking.zip` file in the project root.

## GitHub Actions

This project includes a GitHub Action workflow to automate releases. When a new tag is pushed in the format `v*.*.*`, the workflow will:

1.  Verify that the tag version matches the plugin version in `umami-tracking.php`.
2.  Build a distributable zip file of the plugin.
3.  Create a new GitHub release with the zip file as an asset.

## License

GPL v2 or later.

<!-- tooling:start (managed by wordpress-plugin-boilerplate/tooling - do not edit by hand) -->
## Development

This repository uses the standard Fullworks free-plugin tooling, documented in
[wordpress-plugin-boilerplate](https://github.com/alanef/wordpress-plugin-boilerplate/blob/main/CLAUDE.md).

[![Plugin Check](https://github.com/alanef/umami-tracking/actions/workflows/checks.yml/badge.svg)](https://github.com/alanef/umami-tracking/actions/workflows/checks.yml)

```
umami-tracking/                     # repository root: development tooling
├── .github/workflows/             # checks.yml on push/PR, release.yml on tag
├── tests/                         # PHPUnit suite, run inside wp-env
├── .wp-env.json                   # dev :8414, tests :8415
├── composer.json                  # dev dependencies and quality scripts
├── package.json                   # wp-env and test scripts
├── phpunit.xml.dist / run-tests.sh
└── umami-tracking/                # the plugin (shipped as-is via .distignore)
```

```bash
composer install && npm install        # dev tools
npm run start                          # http://localhost:8414  (admin / password)
composer run check                     # PHPCompatibility + security sniffs
npm test                               # PHPUnit in the wp-env tests container
composer run build                     # zipped/umami-tracking-free.zip
```

Changes go in `umami-tracking/changelog.txt` under `= Unreleased =`. Releases run through
`/tk-release X.Y.Z` then `/tk-deploy live` (see CLAUDE.md): the tag makes CI build the zip,
create the GitHub release and deploy wherever the readme `Type:` says.
<!-- tooling:end -->
