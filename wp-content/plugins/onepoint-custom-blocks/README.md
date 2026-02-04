# Onepoint Custom Blocks

One plugin, one Node.js setup, **@wordpress/scripts only** (no custom webpack).

## Build (required)

From this directory:

```bash
npm install
npm run build
```

This runs `wp-scripts build` once per block (each `blocks/*/src/` → `blocks/*/build/`). No custom webpack, no per-block package.json.

## Adding a new block

1. Create `blocks/{block-name}/` with:
   - `block.json` (with `editorScript`: `file:./build/index.js`, etc.)
   - `src/index.js`, `src/edit.js`, `src/style.css`
2. Run `npm run build` at plugin root. Each block with `src/index.js` is built.
