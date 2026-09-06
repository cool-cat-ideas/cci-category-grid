# CCI Category Grid hooks reference

Version 1.0.0 consumes standard PrestaShop hooks and does not dispatch custom
hooks. The output and asset hooks form one workflow: `displayHome` renders the
section, while a frontend media hook registers its CSS.

## Output stage: `displayHome`

Handled by `hookDisplayHome($params)`.

- Parameters: `array<string,mixed> $params`, supplied by PrestaShop.
- Returns: `string`, the complete category-grid HTML or its empty state.
- Side effects: reads saved module configuration and current language/shop
  context; it does not modify catalog records.

Use **Design > Positions** to move the module within this hook. A theme can call
the standard hook normally:

```smarty
{hook h='displayHome'}
```

## Asset stage: `actionFrontControllerSetMedia`

Handled by `hookActionFrontControllerSetMedia($params)`.

- Parameters: `array<string,mixed> $params`.
- Returns: no content.
- Side effects: registers the base stylesheet and, when present, the active
  theme stylesheet extension.

PrestaShop invokes this hook during frontend asset collection. Do not call it
from a template.

## Compatibility asset hooks: `displayHeader` and `header`

`hookDisplayHeader($params)` and `hookHeader($params)` call the same stylesheet
registration routine for themes or controllers using the legacy header path.

- Parameters: `array<string,mixed> $params`.
- Returns: no content.
- Side effects: registers styles under the module's fixed asset handles.

When integrating a custom frontend controller, keep the standard media lifecycle
instead of invoking all three methods manually.

