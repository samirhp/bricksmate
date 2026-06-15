# Changelog

All notable changes to BricksMate are documented in this file.

## v2.0.1

### Fixed
- Module toggles in the settings panel's left list now enable/disable correctly.

### Changed
- The detail panel on the right shows only the description and example (the toggle lives in the module list).

## v2.0.0 — The full BricksMate suite

BricksMate evolves from a single BEM class generator into a complete toolkit
for Bricks Builder, with a redesigned settings panel and a design-system look.

### Added
- **Settings panel (master–detail):** browse modules on the left; each one shows a plain-English description and a live example on the right. Enable/disable with a toggle and one-click save.
- **New modules** (alongside the original BEM Generator):
  - **Style Indicators** — colored bar per element in the structure tree (blue = styles via CSS class, red = styles via ID).
  - **Sidebar Shortcuts** — quick-insert rail; now fully configurable (add, remove and drag-to-reorder which elements appear).
  - **HTML Tags** — change an element's semantic tag from the structure panel.
  - **Auto-Select Class** — selects the class automatically after create/rename.
  - **Export ID Styles to Class** — moves ID-level styles to the first CSS class.
  - **CSS Recipes** — expand shortcuts like `@clickable-parent;` into full CSS, with the full recipe list shown in the panel.
  - **Expand Children** — expand/collapse an element's entire subtree at once.
  - **Smart Values** — press Enter to turn `--space-m` into `var(--space-m)`, and math like `var(--x) * 2` into `calc(var(--x) * 2)`.
- **Copy styles** toggle in the BEM modal for "create new & remove/delete old".
- **Design-system theme:** dark UI with the BricksMate purple accent, brand logo in the panel header and a native-style isotype in the Bricks toolbar.
- **Accessibility:** visible focus rings, ARIA labels and keyboard activation.
- Subtle cross-promo linking to the BricksMate DS app.

### Changed
- Complete UI redesign aligned with the BricksMate design system; all design tokens centralized (single source of truth) and the whole UI in English.
- Sidebar Shortcuts rail uses native Bricks icons and blends with the panel.
- Plugin renamed to **BricksMate** (formerly "Bricks BEM Generator").
- Shared logic centralized (single structure observer, native input setter).
