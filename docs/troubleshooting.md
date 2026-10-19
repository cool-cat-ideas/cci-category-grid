# Fix common CCI Category Grid problems

## The grid is not visible

Check these points in order:

1. Open **Modules > Module Manager** and confirm the module is enabled.
2. Open its settings, select at least one active category and save.
3. Open **Design > Positions** and confirm the module is attached to `displayHome`.
4. Clear the PrestaShop cache and any hosting or CDN cache.
5. Open the homepage in a private browser window.

If the settings page shows an empty-state message but the homepage shows nothing, the theme may not render `displayHome`.

## One category is missing

Open that category in the catalog and check its active state, shop assignment and translation. The home and root categories are not shown.

## An image is missing

1. Confirm **Show category image** is on.
2. Open the category and confirm it has an image.
3. Regenerate category thumbnails in **Design > Image Settings**.
4. Clear the image and page cache.

## Subcategories are missing

Turn on **Show subcategories** and set the limit above `0`. Only active direct child categories appear.

## The layout looks different from the settings preview

Open the page in a private window first. If the problem remains, temporarily disable the theme override for this module and compare the result. A theme rule for links, images or grid containers may be changing the module output.
