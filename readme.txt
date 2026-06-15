=== BricksMate ===
Contributors: samirh
Tags: bricks, bricks builder, bem, css, design system, page builder
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Your personal toolkit for Bricks Builder: BEM generator, CSS recipes, smart values, configurable shortcuts and more — with a clean, explained settings panel.

== Description ==

BricksMate is a suite of tools that extend the Bricks Builder editor. It adds a
master–detail settings panel where every module is explained with a live
example, so you always know what each tool does before enabling it.

Each module can be toggled on or off independently, and the whole UI follows the
BricksMate design system (dark theme, purple accent).

**Included modules**

* **BEM Generator** — generate and rename CSS classes with BEM naming in bulk.
* **Style Indicators** — colored bar per element (blue = styles via CSS class, red = styles via ID).
* **Sidebar Shortcuts** — a configurable quick-insert rail (add, remove and reorder elements).
* **HTML Tags** — change an element's semantic tag from the structure panel.
* **Auto-Select Class** — selects the class automatically after create/rename.
* **Export ID Styles to Class** — move ID-level styles to the first CSS class.
* **CSS Recipes** — expand shortcuts like `@clickable-parent;` into full CSS.
* **Expand Children** — expand or collapse an element's entire subtree at once.
* **Smart Values** — press Enter to turn `--space-m` into `var(--space-m)`, and `var(--x) * 2` into `calc(var(--x) * 2)`.

== Installation ==

1. Upload the plugin ZIP via **Plugins → Add New → Upload Plugin**, or copy the
   `bricksmate` folder to `wp-content/plugins/`.
2. Activate **BricksMate** through the **Plugins** screen.
3. Open the Bricks Builder editor — a BricksMate icon appears in the top toolbar.
4. Click it to open the settings panel and enable the modules you want.

Updates are delivered automatically from the official GitHub repository; no
manual re-upload is needed for future versions.

== Frequently Asked Questions ==

= Does it require Bricks Builder? =
Yes. BricksMate runs only inside the Bricks Builder editor (Bricks 2.x, tested with 2.3.7).

= Where are the settings? =
Inside the Bricks editor, click the BricksMate icon in the top toolbar. The
panel lists every module with a description and an example.

= Can I turn modules off? =
Yes. Every module has its own toggle; save and reload to apply.

= How do updates work? =
BricksMate checks its GitHub repository and offers updates straight from the
WordPress Plugins screen, like any other plugin.

== Changelog ==

= 2.0.0 =
**The full BricksMate suite.** BricksMate evolves from a single BEM class generator into a complete toolkit for Bricks Builder, with a redesigned settings panel and a design-system look.

Added:

* Settings panel (master–detail): browse modules on the left; each one shows a plain-English description and a live example on the right. Enable/disable with a toggle and one-click save.
* New modules alongside the original BEM Generator: Style Indicators, Sidebar Shortcuts (now fully configurable — add, remove and drag-to-reorder), HTML Tags, Auto-Select Class, Export ID Styles to Class, CSS Recipes (with the full recipe list in the panel), Expand Children and Smart Values.
* Copy styles toggle in the BEM modal for "create new & remove/delete old".
* Design-system theme: dark UI with the BricksMate purple accent, brand logo in the panel header and a native-style isotype in the Bricks toolbar.
* Accessibility: visible focus rings, ARIA labels and keyboard activation.
* Subtle cross-promo linking to the BricksMate DS app.

Changed:

* Complete UI redesign aligned with the BricksMate design system; all design tokens centralized (single source of truth) and the whole UI in English.
* Sidebar Shortcuts rail uses native Bricks icons and blends with the panel.
* Plugin renamed to BricksMate (formerly "Bricks BEM Generator").
* Shared logic centralized (single structure observer, native input setter).

== Upgrade Notice ==

= 2.0.0 =
Major update: the BEM generator becomes the full BricksMate suite with a new settings panel. Your existing BEM workflow keeps working.
