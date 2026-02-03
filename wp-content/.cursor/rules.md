# Cursor Rules – OnePoint WordPress Blocks Project

## Project Scope & Architecture
- This repository contains ONLY `wp-content`
- Do NOT create or modify WordPress core files:
  - wp-admin
  - wp-includes
- Custom Gutenberg blocks MUST live inside:
  wp-content/plugins/onepoint-custom-blocks/blocks/{block-name}/
- Theme is used ONLY for:
  - Layout
  - Templates
  - Styles
  - theme.json
- Business logic and block functionality MUST NOT go into the theme

## Gutenberg Block Architecture
- All Gutenberg blocks MUST be registered using `block.json`
- Block scripts and styles MUST be declared ONLY in `block.json`
- Use file-based asset references only:
  - "file:./index.js"
  - "file:./edit.js"
  - "file:./view.js" (only if required)
  - "file:./style.css"
- Each block MUST be fully self-contained in its own folder
- Do NOT hardcode block names anywhere in PHP

## Plugin PHP Responsibilities (Strict)
- Plugin PHP must NEVER enqueue or register block scripts or styles
- Plugin PHP responsibilities are limited to:
  - Auto-registering blocks via `register_block_type()`
  - Providing `render_callback` ONLY for dynamic blocks
- Always protect files with:
  defined('ABSPATH') || exit;

## Block Folder Rules
- Every block MUST include:
  - block.json
  - index.js
  - edit.js
  - style.css
- Include `view.js` ONLY if frontend JavaScript is required
- Do NOT create empty folders
- Do NOT manually edit the `build/` folder
- Source code changes MUST be made only in `src/`

## JavaScript & Build Rules
- Use `@wordpress/scripts`
- Use ESNext syntax
- Use React hooks from `@wordpress/element`
- Use WordPress UI components from `@wordpress/components`
- Do NOT introduce:
  - Custom webpack configs
  - New bundlers
  - External JS libraries unless explicitly approved

## PHP Coding Standards
- Follow WordPress coding standards
- Sanitize all block attributes
- Escape all output
- Use `add_action` / `add_filter` properly
- No direct file access without ABSPATH check

## Content Editor Experience
- Blocks MUST expose editable controls via `InspectorControls`
- Avoid hardcoded text inside blocks
- Blocks should be reusable and configurable
- Optimize UX for non-technical content editors

## Git & Collaboration Rules
- Only `wp-content` is tracked in Git
- Never reference or depend on `wp-admin` or `wp-includes`
- Assume multiple developers will work on this repository
- Follow existing patterns; do not introduce new systems

## Communication
- If structural or architectural changes are required:
  - Explain WHY before applying changes

## Strict Mode
- If unsure, ASK before creating new files
- Prefer existing patterns over new abstractions

















# Versioning Rules – OnePoint Custom Blocks

## Plugin Versioning
- Plugin version MUST follow Semantic Versioning:
  MAJOR.MINOR.PATCH (e.g. 1.2.3)

### When to bump versions
- PATCH (x.x.1)
  - Bug fixes
  - CSS tweaks
  - Internal refactors
  - No editor-facing changes

- MINOR (x.1.0)
  - New block added
  - New optional attribute
  - New editor controls
  - Backward-compatible changes

- MAJOR (1.0.0)
  - Breaking block markup
  - Attribute removal or rename
  - Block behavior change affecting existing content

- Plugin version MUST be updated in:
  - Plugin PHP header
  - CHANGELOG.md (if present)

## Block Versioning
- Each block MUST declare its own version in `block.json`
- Block version is independent of plugin version

### Block version bump rules
- PATCH
  - Styling fixes
  - Minor JS logic improvements

- MINOR
  - New attribute
  - New Inspector control

- MAJOR
  - Attribute removal/change
  - HTML structure change
  - Frontend behavior change

## Compatibility Rules
- Old block content MUST continue to render
- If breaking change is required:
  - Introduce deprecated block version
  - Or provide migration logic

## Git Tagging
- Tag releases using plugin version:
  v1.0.0
  v1.1.0
  v2.0.0



# Release Checklist – OnePoint WordPress Blocks

## Development
- [ ] Code committed to feature branch
- [ ] Block works in editor
- [ ] Block renders correctly on frontend
- [ ] No PHP warnings or console errors
- [ ] Attributes sanitized & output escaped
- [ ] No hardcoded content
- [ ] Build completed successfully (`npm run build`)

## Pre-Staging
- [ ] Plugin version bumped
- [ ] Block versions updated if required
- [ ] No changes in build/ committed manually
- [ ] Git branch merged to main/develop
- [ ] Git tag created

## Staging
- [ ] Plugin updated on staging site
- [ ] Existing pages tested (regression check)
- [ ] New blocks tested by content editors
- [ ] Responsive check (desktop / tablet / mobile)
- [ ] Performance check (no unnecessary assets loaded)

## Production
- [ ] Backup taken
- [ ] Plugin deployed
- [ ] Cache cleared
- [ ] Smoke test on live pages
- [ ] Editor confirmation received

## Post-Release
- [ ] Monitor logs
- [ ] Capture feedback
- [ ] Patch release planned if needed




# CI Rules – OnePoint Gutenberg Blocks

## Build Rules
- `npm install` MUST be run before build
- `npm run build` MUST succeed without errors
- `build/` directory MUST be generated via build only
- Do NOT manually edit files inside `build/`

## CI Validation (Recommended)
- Fail CI if:
  - build fails
  - block.json is missing
  - required files are missing
  - PHP syntax errors exist

## Block Validation Rules
- Every block folder must contain:
  - block.json
  - index.js
  - edit.js
  - style.css

## CI Must NOT
- Modify WordPress core
- Generate new folders without approval
- Introduce new dependencies automatically

## Optional CI Enhancements
- Lint JS (ESLint)
- Lint PHP (PHPCS – WordPress standard)
- Validate block.json schema

# Content Editor Safety Rules

## Editor Experience
- Blocks MUST be:
  - Drag-and-drop friendly
  - Clearly named
  - Visually identifiable in editor

## Controls & Defaults
- Every block MUST have safe defaults
- No required fields that break rendering
- Fallback UI must exist for empty content

## Restrictions
- Disable raw HTML editing (`supports.html = false`)
- Prevent layout-breaking inputs
- Limit free-text where structured data is expected

## UX Safeguards
- Labels must be human-readable
- Help text must explain purpose
- Avoid technical terms in Inspector controls

## Error Handling
- Block must render gracefully if:
  - Images are missing
  - Attributes are empty
  - Content is partially configured

## Training Safety
- Blocks should be self-explanatory
- No documentation should be required to use basic features






## Theme Rules – OnePoint Block Theme

## Theme Responsibility (Strict)
- Theme is responsible ONLY for:
  - Layout
  - Templates
  - Global styles
  - theme.json configuration
- Theme MUST NOT contain:
  - Custom Gutenberg blocks
  - Block business logic
  - Block JavaScript
  - Block PHP logic

## Theme File Scope
- Allowed files:
  - style.css
  - index.php
  - functions.php
  - theme.json
  - template files (header.php, footer.php, etc.)
- Avoid unnecessary PHP files

## Block Interaction Rules
- Theme MAY:
  - Style blocks using global CSS
  - Adjust spacing, colors, typography via theme.json
- Theme MUST NOT:
  - Modify block markup
  - Override block behavior
  - Register or unregister blocks

## JavaScript in Theme
- Avoid JavaScript in theme
- If JS is required:
  - It must be generic (navigation, UI helpers)
  - It must NOT target block internals
- Block-specific JS belongs in the plugin only

## Styling Rules
- Prefer theme.json for:
  - Colors
  - Typography
  - Spacing
- Do NOT duplicate block styles in theme
- Theme styles should enhance, not override, block styles

## Editor Safety
- Theme MUST NOT:
  - Disable core editor features
  - Remove block controls
- Editor experience must remain consistent across themes

## Update & Maintenance
- Theme updates MUST NOT break blocks
- Blocks must remain functional if theme changes
- Theme should be replaceable without content loss

## Strict Mode
- If unsure whether logic belongs in theme or plugin:
  - Default to PLUGIN
  - Ask before adding logic to theme
