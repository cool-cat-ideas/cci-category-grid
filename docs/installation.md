# Install CCI Category Grid

## Before you start

Make a backup of the shop files and database. Check that the categories you want to show are active and have names in the storefront language.

## Install the module

1. Open **Modules > Module Manager** in PrestaShop.
2. Click **Upload a module**.
3. Choose `cci_categorygrid-1.0.0.zip`.
4. Wait for the installation confirmation.
5. Open **Cool Cat Ideas > CCI Category Grid**.
6. Select the categories you want to show and save.
7. Clear the cache in **Advanced Parameters > Performance**.

Open the homepage. You should see one card for each selected active category.

## If the installation does not finish

- Check that you uploaded the ZIP, not an unpacked folder.
- Check that the ZIP contains the `cci_categorygrid` folder.
- Check that the server uses a PHP version supported by your PrestaShop release.
- Open the PrestaShop log and look for the first error recorded during upload.

## Update or remove the module

Before an update, note the selected category order and back up any theme override. After updating, clear the cache and check the homepage on desktop and mobile.

Uninstalling removes the module settings. It does not delete categories, category images or theme override files.
