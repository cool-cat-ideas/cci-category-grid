# CCI Category Grid PHP API

The supported integration surface is the PrestaShop `WidgetInterface`
implementation. Installation, configuration rendering and private data helpers
are lifecycle internals, not third-party APIs.

## `renderWidget()`

```php
public function renderWidget($hookName = null, array $configuration = [])
```

Use it when compatible PHP code must render this module with its saved settings.

- `$hookName`: `?string`; the calling hook name. `displayHome` is the normal value.
- `$configuration`: `array<string,mixed>`; hook context. Version 1.0.0 does not
  use custom keys from this array.
- Returns: `string`; escaped HTML rendered from the active theme override or the
  bundled Smarty template.
- Side effects: assigns the array from `getWidgetVariables()` to Smarty.

```php
$module = Module::getInstanceByName('cci_categorygrid');

if ($module instanceof Cci_Categorygrid && $module->active) {
    echo $module->renderWidget('displayHome', []);
}
```

## `getWidgetVariables()`

```php
public function getWidgetVariables($hookName = null, array $configuration = [])
```

Use it to obtain the normalized data that the bundled template consumes.

- `$hookName`: `?string`; calling hook name.
- `$configuration`: `array<string,mixed>`; hook context; no custom keys are
  consumed in version 1.0.0.
- Returns: `array<string,mixed>` with the keys below.

| Key | Type | Meaning |
| --- | --- | --- |
| `category_grid_items` | `list<array{id:int,name:string,link:string,image:string,has_image:bool,description:string,sub_categories:list<array{name:string,link:string}>}>` | Active selected categories in saved order. |
| `cci_categorygrid_title` | `string` | Translated section title. |
| `cci_categorygrid_subcategories_label` | `string` | Translated accessible label for child links. |
| `cci_categorygrid_empty_state` | `string` | Translated message used when the list is empty. |
| `cci_categorygrid_show_image` | `bool` | Whether image output is enabled. |
| `cci_categorygrid_show_subcategory` | `bool` | Whether child link output is enabled. |

```php
$module = Module::getInstanceByName('cci_categorygrid');
$variables = $module instanceof Cci_Categorygrid
    ? $module->getWidgetVariables('displayHome', [])
    : [];

foreach ($variables['category_grid_items'] ?? [] as $item) {
    $categoryId = (int) $item['id'];
    // Use the supplied URL and localized name in your own escaped renderer.
}
```

