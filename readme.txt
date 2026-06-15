=== BricksMate ===
Contributors: samirh
Tags: bricks, bricks builder, bem, css, design system
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Your personal toolkit for Bricks Builder: BEM generator, CSS recipes, smart values, configurable shortcuts and more.

== Description ==

BricksMate is a suite of tools that extend the Bricks Builder editor. It adds a master–detail settings panel where every module is explained with a live example, plus a design-system look and feel.

Modules include: BEM Generator, Style Indicators, Sidebar Shortcuts, HTML Tags, Auto-Select Class, Export ID Styles to Class, CSS Recipes, Expand Children and Smart Values.

== Changelog ==

= 2.0.0 =
**The full BricksMate suite.** BricksMate evolves from a single BEM class generator into a complete toolkit for Bricks Builder, with a redesigned settings panel and a design-system look.

Added:

* Settings panel (master–detail): browse modules on the left; each one shows a plain-English description and a live example on the right. Enable/disable with a toggle and one-click save.
* New modules alongside the original BEM Generator: Style Indicators (blue = styles via CSS class, red = styles via ID), Sidebar Shortcuts (now fully configurable — add, remove and drag-to-reorder), HTML Tags, Auto-Select Class, Export ID Styles to Class, CSS Recipes (expand shortcuts like @clickable-parent; into full CSS, with the full recipe list in the panel), Expand Children (expand/collapse an element's entire subtree at once) and Smart Values (Enter turns --space-m into var(--space-m), and var(--x) * 2 into calc(var(--x) * 2)).
* Copy styles toggle in the BEM modal for "create new & remove/delete old".
* Design-system theme: dark UI with the BricksMate purple accent, brand logo in the panel header and a native-style isotype in the Bricks toolbar.
* Accessibility: visible focus rings, ARIA labels and keyboard activation.
* Subtle cross-promo linking to the BricksMate DS app.

Changed:

* Complete UI redesign aligned with the BricksMate design system; all design tokens centralized (single source of truth) and the whole UI in English.
* Sidebar Shortcuts rail uses native Bricks icons and blends with the panel.
* Plugin renamed to BricksMate (formerly "Bricks BEM Generator").
* Shared logic centralized (single structure observer, native input setter).
