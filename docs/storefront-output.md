# Publish and customize the storefront grid

CCI Category Grid renders through the standard PrestaShop `displayHome` hook.
Its position relative to other homepage modules is controlled in
**Design > Positions**.

## Rendered data

Each valid selected category provides:

- numeric category ID;
- localized name and category URL;
- image URL and image-presence flag;
- localized category description;
- zero or more direct active subcategory names and URLs.

The default template renders `.cci-category-grid` with category cards below it.
When no valid category is available, it renders an informational alert instead
of an empty card grid.

## Theme template override

Copy the module template to this path in the active theme:

```text
themes/<theme>/modules/cci_categorygrid/views/templates/hook/cci_categorygrid.tpl
```

The active child theme is checked first and its parent theme second. Keep the
Smarty escaping modifiers when changing markup. Do not edit the template inside
the installed module for a shop-specific layout.

## Theme stylesheet extension

The module always loads:

```text
modules/cci_categorygrid/views/css/cci_categorygrid.css
```

If this file exists in the active theme, it is loaded afterwards:

```text
themes/<theme>/modules/cci_categorygrid/views/css/cci_categorygrid.css
```

Use the later theme file for shop-specific additions. Scope rules under
`.cci-category-grid`; avoid global selectors such as `.container`, `img` or
`a` without the module root.

Stable module classes include `.cci-category-grid-items`,
`.cci-category-grid-card`, `.cci-category-grid-image`,
`.cci-category-grid-content`, `.cci-category-grid-title` and
`.cci-category-grid-subcategories`.

## Translations

The section title, subcategory accessibility label and empty-state message use
the module translation domains. Add translations with the PrestaShop translation
interface, then clear the cache. Category names themselves come from the
catalog's localized category records.

