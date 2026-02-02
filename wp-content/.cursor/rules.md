# Cursor Rules – OnePoint WordPress Blocks Project

## Project Architecture Rules
- Do NOT modify WordPress core files (wp-admin, wp-includes)
- All custom Gutenberg blocks MUST live inside:
  wp-content/plugins/onepoint-custom-blocks/
- Theme is ONLY for layout, styles, templates, and theme.json
- Business logic and block functionality MUST NOT go into the theme

## Gutenberg Block Rules
- Always use block.json for block registration
- Do NOT register blocks inline in PHP unless explicitly requested
- Do NOT manually edit build/ folder
- Source code changes MUST be made only in src/

## JavaScript & Build Rules
- Use @wordpress/scripts conventions
- Use ESNext syntax
- Do NOT introduce new bundlers or custom webpack configs
- Do NOT generate empty folders

## PHP Rules
- Follow WordPress coding standards
- Use add_action / add_filter properly
- No direct file access without ABSPATH check

## Content Editor Experience
- Blocks must expose editable fields via InspectorControls
- Avoid hardcoded text inside blocks
- Blocks should be reusable and configurable

## Git & Repo Rules
- Only wp-content is tracked in git
- Never reference wp-admin or wp-includes in code
- Assume repo will be used by multiple developers

## Communication
- If structure changes are needed, explain WHY before applying

## Strict Mode
- If unsure, ASK before creating new files
- Prefer existing patterns over new abstractions
