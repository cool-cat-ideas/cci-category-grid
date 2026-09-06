# Prepare category-grid demo data

CCI Category Grid does not create, import or delete demo categories. This avoids
mixing sample catalog content with production data.

For a safe demonstration:

1. Use a staging copy of the shop.
2. Create three active top-level catalog categories with localized names and
   images.
3. Add two active direct subcategories below each one.
4. Configure the module to show those three categories, images and at most two
   subcategories.
5. Clear cache and verify the homepage at desktop and mobile widths.
6. Remove only the sample categories you created after the demonstration.

Resetting the module means clearing the selected-category tree and saving. It
does not remove catalog content. Uninstalling also leaves categories and their
images untouched.

