# Composer Package Distribution Plan

## Overview

This document outlines the plan to distribute the WP Local Model Provider plugin as a Composer package via Packagist. The goal is to make the plugin installable via `composer require` while maintaining its functionality as a standard WordPress plugin.

## Current State Analysis

### Existing Setup
- **composer.json**: Already exists with basic configuration
- **Namespace**: `WpLocalModelProvider\` with PSR-4 autoloading
- **Dependencies**: `wordpress/wp-ai-client` ^0.2.1
- **PHP Requirement**: 8.0+
- **Package Name**: `jonathanbossenger/wp-local-model-provider`
- **License**: GPL-2.0-or-later

### Repository Structure
```
wp-local-model-provider/
├── composer.json           # Composer configuration
├── wp-local-model-provider.php  # Main plugin file
├── includes/               # Source code with PSR-4 structure
│   └── Providers/
│       └── Ollama/
├── docs/                   # Documentation
└── README.md              # Plugin documentation
```

## Goals

1. Enable installation via `composer require jonathanbossenger/wp-local-model-provider`
2. Maintain compatibility as a traditional WordPress plugin (manual installation)
3. Ensure proper versioning and semantic versioning compliance
4. Automate releases and Packagist updates
5. Provide clear documentation for both installation methods

## Implementation Plan

### Phase 1: Composer Configuration Enhancement

#### 1.1 Update composer.json

**Required Changes:**
```json
{
    "name": "jonathanbossenger/wp-local-model-provider",
    "description": "Local AI model providers (Ollama) for WordPress AI Client. Enables WordPress to use local AI models without cloud API keys.",
    "type": "wordpress-plugin",
    "license": "GPL-2.0-or-later",
    "keywords": [
        "wordpress",
        "ai",
        "ollama",
        "local",
        "machine-learning",
        "llm",
        "wordpress-plugin"
    ],
    "homepage": "https://github.com/jonathanbossenger/wp-local-model-provider",
    "authors": [
        {
            "name": "Jonathan Bossenger",
            "email": "your-email@example.com",
            "homepage": "https://github.com/jonathanbossenger",
            "role": "Developer"
        }
    ],
    "support": {
        "issues": "https://github.com/jonathanbossenger/wp-local-model-provider/issues",
        "source": "https://github.com/jonathanbossenger/wp-local-model-provider"
    },
    "require": {
        "php": ">=8.0",
        "wordpress/wp-ai-client": "^0.2.1"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.0",
        "squizlabs/php_codesniffer": "^3.7",
        "wp-coding-standards/wpcs": "^3.0"
    },
    "autoload": {
        "psr-4": {
            "WpLocalModelProvider\\": "includes/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "WpLocalModelProvider\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "phpcs": "phpcs --standard=WordPress wp-local-model-provider.php includes/",
        "phpcbf": "phpcbf --standard=WordPress wp-local-model-provider.php includes/"
    },
    "config": {
        "sort-packages": true,
        "allow-plugins": {
            "dealerdirect/phpcodesniffer-composer-installer": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

**Key Additions:**
- Extended description with keywords for discoverability
- Author information
- Support links
- Development dependencies for testing and code standards
- Composer scripts for common tasks
- Plugin configuration

#### 1.2 Add .gitattributes

Create `.gitattributes` to exclude development files from distribution:

```
/.github export-ignore
/tests export-ignore
/docs export-ignore
/.gitattributes export-ignore
/.gitignore export-ignore
/phpunit.xml export-ignore
/phpcs.xml export-ignore
/COMPOSER.md export-ignore
```

#### 1.3 Update .gitignore

Ensure the following are ignored:

```
/vendor/
/node_modules/
composer.lock
.DS_Store
*.log
/phpcs.xml.cache
```

### Phase 2: Versioning Strategy

#### 2.1 Semantic Versioning

Adopt [Semantic Versioning 2.0.0](https://semver.org/):

- **Major (1.x.x)**: Breaking changes, incompatible API changes
- **Minor (x.1.x)**: New features, backwards-compatible
- **Patch (x.x.1)**: Bug fixes, backwards-compatible

**Version Synchronization:**
- Update version in `composer.json` (not typically done, use git tags)
- Update version in `wp-local-model-provider.php` plugin header
- Update `WP_LOCAL_MODEL_PROVIDER_VERSION` constant
- Create git tag matching the version

#### 2.2 Initial Release

Since the plugin is at version 1.0.0, the first Packagist release should be:
- **Version**: 1.0.0
- **Git Tag**: v1.0.0 or 1.0.0
- **Branch**: trunk (main development branch)

#### 2.3 Version Management Workflow

1. Update version in plugin file header
2. Update version constant in plugin file
3. Update CHANGELOG.md with release notes
4. Commit changes: `git commit -m "Release v1.0.0"`
5. Create git tag: `git tag -a v1.0.0 -m "Release version 1.0.0"`
6. Push commits and tags: `git push origin trunk --tags`
7. Packagist auto-updates from GitHub tags

### Phase 3: GitHub Repository Setup

#### 3.1 Repository Configuration

**Required Settings:**
- Repository must be public on GitHub
- Ensure repository URL matches composer.json homepage
- Repository name: `wp-local-model-provider`
- Repository owner: `jonathanbossenger`

#### 3.2 Create CHANGELOG.md

Maintain a changelog following [Keep a Changelog](https://keepachangelog.com/):

```markdown
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-28

### Added
- Initial release
- Ollama provider integration with WordPress AI Client
- Admin settings page for model selection
- Automatic model detection from local Ollama instance
- Public API functions for theme/plugin developers
- Caching for model discovery (5-minute cache)
- Filter hooks for customization

### Documentation
- Quick start guide
- Ollama models reference
- Integration guide for developers
```

#### 3.3 GitHub Release Creation

For each version:
1. Create a GitHub Release matching the git tag
2. Use CHANGELOG.md content for release notes
3. Attach any build artifacts if needed (usually not required)

### Phase 4: Packagist Registration

#### 4.1 Submit to Packagist

**Steps:**
1. Create account on [packagist.org](https://packagist.org)
2. Navigate to "Submit" page
3. Enter GitHub repository URL: `https://github.com/jonathanbossenger/wp-local-model-provider`
4. Click "Check" to validate
5. Submit package

**Validation Requirements:**
- Valid composer.json
- Public repository
- At least one git tag/release
- Valid package name format

#### 4.2 Enable Auto-Update Hook

**GitHub Webhook Setup:**
1. In Packagist, go to your package page
2. Click "Settings" tab
3. Copy the webhook URL
4. In GitHub repository settings:
   - Navigate to Settings > Webhooks
   - Add webhook with Packagist URL
   - Content type: `application/json`
   - Select "Just the push event"
   - Activate webhook

This ensures Packagist auto-updates when you push tags.

#### 4.3 Verify Installation

Test package installation:

```bash
composer require jonathanbossenger/wp-local-model-provider
```

### Phase 5: Documentation Updates

#### 5.1 Update README.md

Add Composer installation as the primary method:

```markdown
## Installation

### Via Composer (Recommended)

```bash
# Install in your WordPress project
composer require jonathanbossenger/wp-local-model-provider
```

The plugin will be installed to `wp-content/plugins/wp-local-model-provider/`.
Activate it through the WordPress admin.

### Via WordPress Plugin Directory

Coming soon to wordpress.org/plugins

### Manual Installation

1. Download the latest release from GitHub
2. Upload to `/wp-content/plugins/wp-local-model-provider/`
3. Run `composer install` in the plugin directory
4. Activate through WordPress admin
```

#### 5.2 Create INSTALLATION.md

Detailed installation guide for different scenarios:
- Composer-based WordPress installations
- Bedrock/Roots.io setup
- Traditional WordPress installations
- Development environment setup

#### 5.3 Add Packagist Badge

Add to README.md:

```markdown
[![Latest Stable Version](https://poser.pugx.org/jonathanbossenger/wp-local-model-provider/v/stable)](https://packagist.org/packages/jonathanbossenger/wp-local-model-provider)
[![Total Downloads](https://poser.pugx.org/jonathanbossenger/wp-local-model-provider/downloads)](https://packagist.org/packages/jonathanbossenger/wp-local-model-provider)
[![License](https://poser.pugx.org/jonathanbossenger/wp-local-model-provider/license)](https://packagist.org/packages/jonathanbossenger/wp-local-model-provider)
```

### Phase 6: CI/CD Automation

#### 6.1 GitHub Actions Workflows

Create `.github/workflows/` directory with automated workflows.

**Workflow: Code Quality (`code-quality.yml`)**

```yaml
name: Code Quality

on:
  push:
    branches: [ trunk ]
  pull_request:
    branches: [ trunk ]

jobs:
  phpcs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
      - name: Install dependencies
        run: composer install
      - name: Run PHPCS
        run: composer run phpcs

  phpstan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
      - name: Install dependencies
        run: composer install
      - name: Run PHPStan
        run: vendor/bin/phpstan analyze
```

**Workflow: Release Automation (`release.yml`)**

```yaml
name: Release

on:
  push:
    tags:
      - 'v*'

jobs:
  release:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Create GitHub Release
        uses: softprops/action-gh-release@v1
        with:
          generate_release_notes: true
          files: |
            README.md
            CHANGELOG.md
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}

      - name: Notify Packagist
        run: |
          curl -X POST https://packagist.org/api/update-package?username=${{ secrets.PACKAGIST_USERNAME }}&apiToken=${{ secrets.PACKAGIST_TOKEN }} \
               -d '{"repository":{"url":"https://github.com/jonathanbossenger/wp-local-model-provider"}}'
```

#### 6.2 Required GitHub Secrets

Add to repository secrets:
- `PACKAGIST_USERNAME`: Your Packagist username
- `PACKAGIST_TOKEN`: API token from Packagist profile

### Phase 7: Testing Infrastructure

#### 7.1 Add PHPUnit Configuration

Create `phpunit.xml`:

```xml
<?xml version="1.0"?>
<phpunit
    bootstrap="tests/bootstrap.php"
    colors="true"
    beStrictAboutTestsThatDoNotTestAnything="true"
    beStrictAboutOutputDuringTests="true">
    <testsuites>
        <testsuite name="WP Local Model Provider Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">includes</directory>
        </whitelist>
    </filter>
</phpunit>
```

#### 7.2 Create Test Bootstrap

Create `tests/bootstrap.php`:

```php
<?php
/**
 * PHPUnit bootstrap file
 */

// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load WordPress test framework
// This would need WordPress test library
// For now, basic autoloading is sufficient
```

#### 7.3 Add Basic Tests

Create `tests/` directory with initial test classes:
- `ProviderTest.php` - Test Ollama provider registration
- `SettingsTest.php` - Test settings functionality
- `IntegrationTest.php` - Test integration with WordPress AI Client

### Phase 8: WordPress-Specific Considerations

#### 8.1 Composer Installers

The `type: wordpress-plugin` in composer.json works with [composer/installers](https://github.com/composer/installers) to automatically place the plugin in the correct directory.

**For Modern WordPress Setups (Bedrock):**

Bedrock automatically handles WordPress plugins:

```bash
composer require jonathanbossenger/wp-local-model-provider
# Installs to web/app/plugins/wp-local-model-provider/
```

**For Traditional WordPress:**

Use composer in wp-content directory:

```bash
cd wp-content/plugins
composer require jonathanbossenger/wp-local-model-provider
```

Or use a custom composer.json in WordPress root:

```json
{
    "require": {
        "jonathanbossenger/wp-local-model-provider": "^1.0"
    },
    "extra": {
        "installer-paths": {
            "wp-content/plugins/{$name}/": ["type:wordpress-plugin"]
        }
    }
}
```

#### 8.2 Handling WordPress Dependencies

Since WordPress itself isn't typically installed via Composer in traditional setups:

1. Keep WordPress functions mockable for testing
2. Use dependency injection where possible
3. Provide clear documentation for both modern and traditional setups

#### 8.3 Plugin Activation

Ensure the plugin works regardless of installation method:

```php
// Check for Composer autoloader
if (file_exists(WP_LOCAL_MODEL_PROVIDER_PATH . 'vendor/autoload.php')) {
    require_once WP_LOCAL_MODEL_PROVIDER_PATH . 'vendor/autoload.php';
} else {
    // Fallback error message if Composer dependencies not installed
    add_action('admin_notices', function() {
        echo '<div class="error"><p>';
        echo 'WP Local Model Provider requires Composer dependencies. ';
        echo 'Run <code>composer install</code> in the plugin directory.';
        echo '</p></div>';
    });
    return;
}
```

### Phase 9: Distribution Strategies

#### 9.1 Multiple Distribution Channels

Target three distribution methods:

1. **Packagist/Composer** (Primary for developers)
   - `composer require jonathanbossenger/wp-local-model-provider`
   - Auto-updates via Packagist webhooks
   - Semantic versioning

2. **GitHub Releases** (Manual downloads)
   - Tagged releases with built packages
   - Include vendor directory in releases
   - Provide ZIP downloads

3. **WordPress.org Plugin Directory** (Future)
   - Submit to wordpress.org/plugins
   - SVN repository management
   - WordPress.org automatic updates

#### 9.2 Pre-built Releases

Create GitHub Action to build release packages:

```yaml
name: Build Release Package

on:
  push:
    tags:
      - 'v*'

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'

      - name: Install Composer dependencies
        run: composer install --no-dev --optimize-autoloader

      - name: Create release archive
        run: |
          zip -r wp-local-model-provider-${{ github.ref_name }}.zip . \
            -x "*.git*" "tests/*" "*.md" "phpunit.xml*" "phpcs.xml*"

      - name: Upload release asset
        uses: softprops/action-gh-release@v1
        with:
          files: wp-local-model-provider-${{ github.ref_name }}.zip
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```

### Phase 10: Launch Checklist

#### Pre-Launch Tasks

- [ ] Complete composer.json with all metadata
- [ ] Create initial git tag (v1.0.0)
- [ ] Add CHANGELOG.md with v1.0.0 entry
- [ ] Create .gitattributes for clean exports
- [ ] Update README.md with Composer installation
- [ ] Set up GitHub repository (if not already done)
- [ ] Verify code follows WordPress Coding Standards

#### Launch Tasks

- [ ] Push v1.0.0 tag to GitHub
- [ ] Create GitHub Release for v1.0.0
- [ ] Register package on Packagist.org
- [ ] Set up GitHub webhook for Packagist
- [ ] Test `composer require` installation
- [ ] Verify package appears on Packagist

#### Post-Launch Tasks

- [ ] Add Packagist badges to README
- [ ] Set up GitHub Actions for CI/CD
- [ ] Monitor initial installations/downloads
- [ ] Gather community feedback
- [ ] Plan future releases

#### Documentation Tasks

- [ ] Create CONTRIBUTING.md
- [ ] Create INSTALLATION.md
- [ ] Update all docs/ files to reference Composer installation
- [ ] Add examples for Bedrock/Roots.io users
- [ ] Document version upgrade procedures

## Maintenance Guidelines

### Version Release Process

1. **Planning**
   - Determine version number (major/minor/patch)
   - Update CHANGELOG.md with changes

2. **Code Updates**
   - Update version in plugin header
   - Update version constant
   - Run code quality checks: `composer run phpcs`
   - Run tests: `composer run test`

3. **Commit and Tag**
   ```bash
   git add .
   git commit -m "Release v1.1.0"
   git tag -a v1.1.0 -m "Release version 1.1.0"
   git push origin trunk --tags
   ```

4. **Verify**
   - Check GitHub Release was created (via Actions)
   - Verify Packagist updated (may take a few minutes)
   - Test installation: `composer require jonathanbossenger/wp-local-model-provider:^1.1`

### Backward Compatibility

Maintain backward compatibility within major versions:

- Keep public API functions unchanged
- Use deprecation notices for 1-2 minor versions before removal
- Document breaking changes clearly in CHANGELOG
- Provide upgrade guides for major versions

### Community Engagement

- Respond to GitHub Issues promptly
- Review Pull Requests
- Update documentation based on user feedback
- Consider feature requests for minor/major versions

## Benefits of Packagist Distribution

### For Developers

1. **Easy Installation**: Single `composer require` command
2. **Dependency Management**: Automatic dependency resolution
3. **Version Control**: Pin to specific versions
4. **Updates**: Simple `composer update` to get latest versions
5. **Integration**: Works with modern WordPress stacks (Bedrock, etc.)

### For Project Maintainers

1. **Automated Distribution**: Push tag, package updates automatically
2. **Semantic Versioning**: Clear version management
3. **CI/CD Integration**: Automated testing and releases
4. **Analytics**: Download statistics via Packagist
5. **Discoverability**: Listed on Packagist.org

### For the Plugin

1. **Professional Distribution**: Industry-standard package management
2. **Wider Reach**: Available to Composer-based WordPress projects
3. **Better Dependency Management**: WordPress AI Client auto-installs
4. **Developer Trust**: Packagist is the standard for PHP packages

## Risks and Mitigation

### Risk: Breaking Changes

**Mitigation:**
- Follow semantic versioning strictly
- Maintain comprehensive CHANGELOG
- Test with multiple WordPress/PHP versions
- Deprecate features before removal

### Risk: Dependency Conflicts

**Mitigation:**
- Keep dependency versions flexible where possible
- Use `^` version constraints for minor updates
- Document compatible version ranges
- Test with different dependency versions

### Risk: Installation Complexity

**Mitigation:**
- Provide clear installation docs for all scenarios
- Include fallback error messages in plugin
- Document both Composer and manual installation
- Create video tutorials if needed

### Risk: Version Drift

**Mitigation:**
- Automate version updates with scripts
- Use GitHub Actions to validate versions match
- Document version update process clearly
- Code review checklist includes version verification

## Success Metrics

Track these metrics post-launch:

1. **Packagist Downloads**: Monitor monthly/total downloads
2. **GitHub Stars**: Measure community interest
3. **Issues/PRs**: Track community engagement
4. **Documentation Views**: Monitor docs/ access
5. **Search Rankings**: Track Packagist search visibility

Target first 90 days:
- 100+ Packagist downloads
- 25+ GitHub stars
- 5+ community issues/PRs
- Documentation for all major use cases

## Conclusion

This plan provides a comprehensive roadmap for distributing WP Local Model Provider as a Composer package via Packagist. The approach maintains compatibility with traditional WordPress installations while embracing modern PHP package management.

**Key Takeaways:**
- Enhance composer.json with complete metadata
- Implement semantic versioning rigorously
- Automate releases with GitHub Actions
- Register and maintain Packagist listing
- Provide excellent documentation for all installation methods

**Next Steps:**
1. Review and approve this plan
2. Begin Phase 1: Composer configuration enhancement
3. Create initial git tag and release
4. Register on Packagist
5. Monitor and iterate based on community feedback
