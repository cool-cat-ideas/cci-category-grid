<?php

/**
 * CCI Category Grid for PrestaShop.
 *
 * @author    Cool Cat Ideas <info@coolcatideas.com>
 * @copyright 2026 Cool Cat Ideas
 * @license   https://opensource.org/license/afl-3-0-php Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once __DIR__ . '/classes/Admin/CciCategoryGridAdminPresentation.php';

class Cci_Categorygrid extends Module implements WidgetInterface
{
    private const ADMIN_PARENT_TAB_CLASS = 'AdminCciParent';
    private const ADMIN_TAB_CLASS = 'AdminCciCategoryGrid';
    private const CONF_SHOW_IMAGE = 'CCI_CATEGORYGRID_SHOW_IMAGE';
    private const CONF_SHOW_SUBCATEGORY = 'CCI_CATEGORYGRID_SHOW_SUBCATEGORY';
    private const CONF_SUBCATEGORY_LIMIT = 'CCI_CATEGORYGRID_SUBCATEGORY_LIMIT';
    private const CONF_SELECTED_CATEGORIES = 'CCI_CATEGORYGRID_SELECTED_CATEGORIES';

    private ?CciCategoryGridAdminPresentation $adminPresentation = null;

    public function __construct()
    {
        $this->name = 'cci_categorygrid';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Cool Cat Ideas';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => '9.2.0',
        ];

        parent::__construct();

        $this->displayName = 'CCI Category Grid';
        $this->description = $this->trans('Choose which categories appear on the PrestaShop homepage and whether each responsive card shows an image and subcategory links.', [], 'Modules.CciCategorygrid.Admin');
        $this->templateFile = 'module:cci_categorygrid/views/templates/hook/cci_categorygrid.tpl';
    }

    public function install()
    {
        return parent::install()
          && $this->setupDefaultConfiguration()
          && $this->registerHook('actionFrontControllerSetMedia')
          && $this->registerHook('displayHeader')
          && $this->registerHook('displayHome')
          && $this->ensureAdminTab();
    }

    public function uninstall()
    {
        return $this->uninstallAdminTab()
          && Configuration::deleteByName(self::CONF_SHOW_IMAGE)
          && Configuration::deleteByName(self::CONF_SHOW_SUBCATEGORY)
          && Configuration::deleteByName(self::CONF_SUBCATEGORY_LIMIT)
          && Configuration::deleteByName(self::CONF_SELECTED_CATEGORIES)
          && parent::uninstall();
    }

    public function getContent()
    {
        $this->ensureAdminTab();
        $this->registerAdminAssets();
        $output = '';
        $sidebar = $this->getAdminPresentation()->renderBrandPanel();

        if ((bool) Tools::isSubmit('submitCciCategorygrid')) {
            $this->saveCategoryGridConfig();
            $output .= $this->displayConfirmation($this->trans('Settings have been saved.', [], 'Modules.CciCategorygrid.Admin'));
        }

        return '<div class="cci-admin-shell cci-categorygrid-shell bootstrap">'
          . '<div class="cci-admin-main">'
          . $output
          . $this->getAdminPresentation()->renderHeroSection()
          . '<div class="cci-card cci-card--settings">'
          . '<div class="cci-card-inner">'
          . '<div class="cci-section-title">'
          . '<h3>' . $this->escapeHtml($this->trans('Category grid settings', [], 'Modules.CciCategorygrid.Admin')) . '</h3>'
          . '</div>'
          . '<p class="cci-subtitle">' . $this->escapeHtml($this->trans('Choose which categories are shown on front office and control the card presentation.', [], 'Modules.CciCategorygrid.Admin')) . '</p>'
          . '<div class="cci-form-wrapper">'
          . $this->renderSettingsForm()
          . '</div>'
          . '</div>'
          . '</div>'
          . '</div>'
          . $sidebar
          . '</div>';
    }

    public function ensureAdminTab(): bool
    {
        $parentId = $this->ensureCciParentTab();
        if ($parentId <= 0) {
            return false;
        }

        $tabId = (int) Tab::getIdFromClassName(self::ADMIN_TAB_CLASS);
        $tab = $tabId > 0 ? new Tab($tabId) : new Tab();
        $tab->active = 1;
        $tab->class_name = self::ADMIN_TAB_CLASS;
        $tab->id_parent = $parentId;
        $tab->icon = 'grid_view';
        $tab->module = $this->name;

        foreach (Language::getLanguages(false) as $language) {
            $tab->name[(int) $language['id_lang']] = 'CCI Category Grid';
        }

        $saved = $tabId > 0 ? (bool) $tab->update() : (bool) $tab->add();
        if (!$saved) {
            return false;
        }

        return $this->normalizeCciMenuPositions($parentId);
    }

    private function ensureCciParentTab(): int
    {
        $improveTabId = (int) Tab::getIdFromClassName('IMPROVE');
        if ($improveTabId <= 0) {
            return 0;
        }

        $tabId = (int) Tab::getIdFromClassName(self::ADMIN_PARENT_TAB_CLASS);
        $tab = $tabId > 0 ? new Tab($tabId) : new Tab();
        $tab->active = 1;
        $tab->class_name = self::ADMIN_PARENT_TAB_CLASS;
        $tab->id_parent = $improveTabId;
        $tab->icon = 'pets';
        $tab->module = '';
        foreach (Language::getLanguages(false) as $language) {
            $tab->name[(int) $language['id_lang']] = 'Cool Cat Ideas';
        }

        $saved = $tabId > 0 ? (bool) $tab->update() : (bool) $tab->add();
        if (!$saved) {
            return 0;
        }

        $modulesTabId = (int) Tab::getIdFromClassName('AdminParentModulesSf');
        if ($modulesTabId > 0) {
            $modulesTab = new Tab($modulesTabId);
            $tab = new Tab((int) $tab->id);
            if ((int) $modulesTab->id_parent === $improveTabId) {
                $desiredPosition = (int) $modulesTab->position + 1;
                if ((int) $tab->position !== $desiredPosition) {
                    $tab->updatePosition((int) $tab->position < $desiredPosition, $desiredPosition);
                }
            }
        }

        $tab->cleanPositions($improveTabId);

        return (int) $tab->id;
    }

    private function normalizeCciMenuPositions(int $parentId): bool
    {
        $tabs = Db::getInstance()->executeS(
            'SELECT `id_tab`, `class_name`, `position` FROM `' . _DB_PREFIX_ . 'tab`'
            . ' WHERE `id_parent` = ' . $parentId
        );
        if (!is_array($tabs)) {
            return false;
        }

        $priorities = [
            'AdminCciBlog' => 10,
            'AdminCciWithdrawal' => 20,
            'AdminCciConsentHistory' => 25,
            'AdminCciNiceMenu' => 30,
            'AdminCciCategoryGrid' => 40,
            'AdminCciCourierCashOnDelivery' => 50,
        ];
        usort($tabs, static function (array $left, array $right) use ($priorities): int {
            $leftPriority = $priorities[$left['class_name']] ?? 1000 + (int) $left['position'];
            $rightPriority = $priorities[$right['class_name']] ?? 1000 + (int) $right['position'];

            return $leftPriority <=> $rightPriority ?: (int) $left['id_tab'] <=> (int) $right['id_tab'];
        });

        foreach ($tabs as $index => $tabData) {
            if (!Db::getInstance()->update('tab', ['position' => $index + 1], 'id_tab = ' . (int) $tabData['id_tab'])) {
                return false;
            }
        }

        return true;
    }

    private function uninstallAdminTab(): bool
    {
        $tabId = (int) Tab::getIdFromClassName(self::ADMIN_TAB_CLASS);
        if ($tabId > 0 && !(bool) (new Tab($tabId))->delete()) {
            return false;
        }

        $parentId = (int) Tab::getIdFromClassName(self::ADMIN_PARENT_TAB_CLASS);
        if ($parentId <= 0) {
            return true;
        }

        $remainingChildren = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'tab` WHERE `id_parent` = ' . $parentId
        );

        return $remainingChildren > 0 || (bool) (new Tab($parentId))->delete();
    }

    public function hookDisplayHeader($params)
    {
        $this->registerFrontAssets();
    }

    public function hookHeader($params)
    {
        $this->registerFrontAssets();
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $this->registerFrontAssets();
    }

    private function registerFrontAssets(): void
    {
        $controller = $this->context->controller;
        if (!is_object($controller) || !method_exists($controller, 'registerStylesheet')) {
            return;
        }

        $baseCssFile = __DIR__ . '/views/css/cci_categorygrid.css';

        $controller->registerStylesheet(
            'cci-categorygrid-base',
            $this->getPathUri() . 'views/css/cci_categorygrid.css',
            [
                'media' => 'all',
                'priority' => 200,
                'server' => 'remote',
                'version' => $this->getAssetVersion($baseCssFile),
            ]
        );

        $themeCssAsset = $this->getThemeAsset('views/css/cci_categorygrid.css');
        if ($themeCssAsset !== null) {
            $controller->registerStylesheet(
                'cci-categorygrid-theme',
                $themeCssAsset['url'],
                ['media' => 'all', 'priority' => 210, 'version' => $this->getAssetVersion($themeCssAsset['file'])]
            );
        }
    }

    public function hookDisplayHome($params)
    {
        return $this->renderWidget('displayHome', $params);
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        $templateFile = $this->getThemeTemplateFilePath();

        return $this->fetch($templateFile);
    }

    private function getThemeTemplateFilePath(): string
    {
        $themeTemplate = _PS_THEME_DIR_ . 'modules/' . $this->name . '/views/templates/hook/cci_categorygrid.tpl';
        if (Tools::file_exists_cache($themeTemplate)) {
            return $themeTemplate;
        }

        if (_PS_PARENT_THEME_DIR_ !== '') {
            $parentThemeTemplate = _PS_PARENT_THEME_DIR_ . 'modules/' . $this->name . '/views/templates/hook/cci_categorygrid.tpl';
            if (Tools::file_exists_cache($parentThemeTemplate)) {
                return $parentThemeTemplate;
            }
        }

        $templatePath = $this->getTemplatePath('cci_categorygrid.tpl');
        if (is_string($templatePath) && $templatePath !== '') {
            return $templatePath;
        }

        return $this->templateFile;
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $idLang = (int) Context::getContext()->language->id;
        $idShop = (int) Context::getContext()->shop->id;

        $showImage = (bool) Configuration::get(self::CONF_SHOW_IMAGE);
        $showSubCategory = (bool) Configuration::get(self::CONF_SHOW_SUBCATEGORY);
        $subCategoryLimit = (int) Configuration::get(self::CONF_SUBCATEGORY_LIMIT);
        $subCategoryLimit = max(0, $subCategoryLimit);

        return [
            'category_grid_items' => $this->getCategoryGridItems($idLang, $idShop, $showImage, $showSubCategory, $subCategoryLimit),
            'cci_categorygrid_title' => $this->resolveFrontTranslation('Product categories', $this->trans('Product categories', [], 'Modules.CciCategorygrid.Shop')),
            'cci_categorygrid_subcategories_label' => $this->resolveFrontTranslation('Subcategories', $this->trans('Subcategories', [], 'Modules.CciCategorygrid.Shop')),
            'cci_categorygrid_empty_state' => $this->resolveFrontTranslation('No categories selected for the CCI Category Grid module.', $this->trans('No categories selected for the CCI Category Grid module.', [], 'Modules.CciCategorygrid.Shop')),
            'cci_categorygrid_show_image' => $showImage,
            'cci_categorygrid_show_subcategory' => $showSubCategory,
        ];
    }

    private function getCategoryGridItems($idLang, $idShop, $showImage, $showSubCategory, $subCategoryLimit)
    {
        $items = [];
        $categoryIds = $this->normalizeCategoryIds(Configuration::get(self::CONF_SELECTED_CATEGORIES));

        if (!$categoryIds) {
            return $items;
        }

        foreach ($categoryIds as $categoryId) {
            $category = new Category((int) $categoryId, (int) $idLang, (int) $idShop);
            if (!$category || empty($category->id) || (int) $category->id === 1 || empty($category->active)) {
                continue;
            }

            $subCategories = [];
            if ($showSubCategory && $subCategoryLimit > 0) {
                $children = $category->getSubCategories((int) $idLang, true);
                if (is_array($children)) {
                    $children = array_slice($children, 0, $subCategoryLimit);
                    foreach ($children as $child) {
                        if (empty($child['id_category']) || empty($child['link_rewrite']) || empty($child['name'])) {
                            continue;
                        }

                        if ((int) $child['active'] !== 1) {
                            continue;
                        }

                        $subCategories[] = [
                            'name' => (string) $child['name'],
                            'link' => $this->context->link->getCategoryLink((int) $child['id_category'], $child['link_rewrite']),
                        ];
                    }
                }
            }

            $items[] = [
                'id' => (int) $category->id,
                'name' => (string) $category->name,
                'link' => $this->context->link->getCategoryLink($category, $category->link_rewrite),
                'image' => $showImage ? $this->getCategoryImageUrl($category, ['category_thumb', ImageType::getFormattedName('category'), 'small_default']) : '',
                'has_image' => (bool) $category->id_image,
                'description' => (string) $category->description,
                'sub_categories' => $subCategories,
            ];
        }

        return $items;
    }

    private function getCategoryImageUrl(Category $category, array $categoryTypes): string
    {
        if ((int) $category->id === 0 || empty($category->link_rewrite)) {
            return '';
        }

        $categoryTypes = array_values(array_filter(array_unique(array_map('strval', $categoryTypes))));
        if (!$categoryTypes) {
            $categoryTypes = [ImageType::getFormattedName('category')];
        }

        foreach ($categoryTypes as $categoryType) {
            $imageBasePath = _PS_IMG_DIR_ . 'c/' . (int) $category->id . '-' . $categoryType . '.';

            foreach ($this->getPreferredCategoryImageExtensions() as $extension) {
                $imageFile = $imageBasePath . $extension;
                if (@is_file($imageFile)) {
                    return $this->context->link->getCatImageLink($category->link_rewrite, (int) $category->id, $categoryType, $extension);
                }
            }
        }

        return $this->context->link->getCatImageLink($category->link_rewrite, (int) $category->id, $categoryTypes[0]);
    }

    private function getPreferredCategoryImageExtensions(): array
    {
        $configQuality = (string) Configuration::get('PS_IMAGE_QUALITY');
        $configQuality = strtolower(trim($configQuality));

        if (in_array($configQuality, ['webp', 'png', 'jpg', 'jpeg'], true)) {
            if ($configQuality === 'webp') {
                return ['webp', 'jpg', 'png', 'jpeg'];
            }

            if ($configQuality === 'png') {
                return ['png', 'webp', 'jpg', 'jpeg'];
            }
        }

        return ['webp', 'jpg', 'png', 'jpeg'];
    }

    private function getThemeAsset(string $relativePath): ?array
    {
        $moduleName = $this->name;
        $theme = isset($this->context->shop->theme) ? $this->context->shop->theme : null;

        if (is_object($theme)) {
            $themeName = (string) $theme->getName();
            if ($themeName !== '') {
                $themeAssetPath = _PS_THEME_DIR_ . 'modules/' . $moduleName . '/' . $relativePath;
                if (Tools::file_exists_cache($themeAssetPath)) {
                    return [
                        'url' => 'themes/' . $themeName . '/modules/' . $moduleName . '/' . $relativePath,
                        'file' => $themeAssetPath,
                    ];
                }
            }

            $parentThemeName = (string) $theme->get('parent');
            if ($parentThemeName !== '') {
                $parentThemeDir = _PS_ROOT_DIR_ . '/themes/' . $parentThemeName . '/modules/' . $moduleName . '/' . $relativePath;
                if (Tools::file_exists_cache($parentThemeDir)) {
                    return [
                        'url' => 'themes/' . $parentThemeName . '/modules/' . $moduleName . '/' . $relativePath,
                        'file' => $parentThemeDir,
                    ];
                }
            }
        }

        return null;
    }

    private function getAssetVersion(string $filePath): string
    {
        return (string) (@filemtime($filePath) ?: $this->version);
    }

    private function registerAdminAssets(): void
    {
        $cssFile = __DIR__ . '/views/css/cci_categorygrid_admin.css';
        $cssVersion = (string) (@filemtime($cssFile) ?: $this->version);
        $controller = $this->context->controller;

        if (method_exists($controller, 'registerStylesheet')) {
            $controller->registerStylesheet(
                'cci-categorygrid-admin',
                'modules/' . $this->name . '/views/css/cci_categorygrid_admin.css?v=' . rawurlencode($cssVersion),
                ['media' => 'all', 'priority' => 230]
            );
        } elseif (method_exists($controller, 'addCSS')) {
            $controller->addCSS($this->getPathUri() . 'views/css/cci_categorygrid_admin.css?v=' . rawurlencode($cssVersion), 'all');
        }
    }

    private function escapeHtml($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private function transCore($id, $domain = 'Admin.Actions')
    {
        try {
            return Context::getContext()->getTranslator()->trans((string) $id, [], $domain);
        } catch (Throwable $e) {
            return (string) $id;
        }
    }

    private function renderSettingsForm()
    {
        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Category grid settings', [], 'Modules.CciCategorygrid.Admin'),
                    'icon' => 'icon-th-large',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Show Image Category', [], 'Modules.CciCategorygrid.Admin'),
                        'name' => self::CONF_SHOW_IMAGE,
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'image_on',
                                'value' => 1,
                                'label' => $this->transCore('Enabled', 'Admin.Global'),
                            ],
                            [
                                'id' => 'image_off',
                                'value' => 0,
                                'label' => $this->transCore('Disabled', 'Admin.Global'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Show Sub Category', [], 'Modules.CciCategorygrid.Admin'),
                        'name' => self::CONF_SHOW_SUBCATEGORY,
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'sub_on',
                                'value' => 1,
                                'label' => $this->transCore('Enabled', 'Admin.Global'),
                            ],
                            [
                                'id' => 'sub_off',
                                'value' => 0,
                                'label' => $this->transCore('Disabled', 'Admin.Global'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Number Sub Category', [], 'Modules.CciCategorygrid.Admin'),
                        'name' => self::CONF_SUBCATEGORY_LIMIT,
                        'desc' => $this->trans('How many subcategories to show under each parent category.', [], 'Modules.CciCategorygrid.Admin'),
                    ],
                    [
                        'type' => 'categories',
                        'label' => $this->trans('Check Categories', [], 'Modules.CciCategorygrid.Admin'),
                        'name' => self::CONF_SELECTED_CATEGORIES,
                        'desc' => $this->trans('Choose categories to show in the grid.', [], 'Modules.CciCategorygrid.Admin'),
                        'tree' => [
                            'id' => 'categories-tree',
                            'selected_categories' => $this->getSelectedCategoriesConfigForTree(),
                            'use_checkbox' => true,
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->transCore('Save', 'Admin.Actions'),
                    'name' => 'submitCciCategorygrid',
                    'icon' => 'icon-save',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCciCategorygrid';
        $helper->currentIndex = $this->context->link->getAdminLink(self::ADMIN_TAB_CLASS, false);
        $helper->token = Tools::getAdminTokenLite(self::ADMIN_TAB_CLASS);
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $this->flattenHelperFormPanel($helper->generateForm([$fieldsForm]));
    }

    private function flattenHelperFormPanel(string $formHtml): string
    {
        $flattened = preg_replace(
            '~<div class="panel" id="fieldset_[^"]*">\s*<div class="panel-heading">.*?</div>~s',
            '<div class="panel cci-helper-form-content">',
            $formHtml,
            1
        );

        return is_string($flattened) ? $flattened : $formHtml;
    }

    private function saveCategoryGridConfig()
    {
        $showImage = (bool) Tools::getValue(self::CONF_SHOW_IMAGE, 0);
        $showSubCategory = (bool) Tools::getValue(self::CONF_SHOW_SUBCATEGORY, 0);
        $subCategoryLimit = (int) Tools::getValue(self::CONF_SUBCATEGORY_LIMIT, 0);

        if ($subCategoryLimit < 0) {
            $subCategoryLimit = 0;
        }

        if ($subCategoryLimit > 30) {
            $subCategoryLimit = 30;
        }

        $selectedCategories = Tools::getValue(self::CONF_SELECTED_CATEGORIES, []);

        Configuration::updateValue(self::CONF_SHOW_IMAGE, (int) $showImage);
        Configuration::updateValue(self::CONF_SHOW_SUBCATEGORY, (int) $showSubCategory);
        Configuration::updateValue(self::CONF_SUBCATEGORY_LIMIT, (int) $subCategoryLimit);
        Configuration::updateValue(self::CONF_SELECTED_CATEGORIES, json_encode($this->normalizeCategoryIds($selectedCategories)));
    }

    private function setupDefaultConfiguration()
    {
        return Configuration::updateValue(self::CONF_SHOW_IMAGE, 1)
          && Configuration::updateValue(self::CONF_SHOW_SUBCATEGORY, 1)
          && Configuration::updateValue(self::CONF_SUBCATEGORY_LIMIT, 5)
          && Configuration::updateValue(self::CONF_SELECTED_CATEGORIES, json_encode([]));
    }

    private function getConfigFormValues()
    {
        $selected = Configuration::get(self::CONF_SELECTED_CATEGORIES);

        return [
            self::CONF_SHOW_IMAGE => (int) Configuration::get(self::CONF_SHOW_IMAGE),
            self::CONF_SHOW_SUBCATEGORY => (int) Configuration::get(self::CONF_SHOW_SUBCATEGORY),
            self::CONF_SUBCATEGORY_LIMIT => (int) Configuration::get(self::CONF_SUBCATEGORY_LIMIT),
            self::CONF_SELECTED_CATEGORIES => $this->normalizeCategoryIds($selected),
        ];
    }

    private function getSelectedCategoriesConfigForTree()
    {
        return $this->normalizeCategoryIds(Configuration::get(self::CONF_SELECTED_CATEGORIES));
    }

    private function normalizeCategoryIds($value = null)
    {
        $ids = [];
        if (is_array($value)) {
            $ids = $value;
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $ids = $decoded;
            } elseif ($value !== '') {
                $ids = preg_split('/[;,\s]+/', trim($value));
            }
        }

        if (!is_array($ids)) {
            return [];
        }

        $normalized = [];
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 2 && !in_array($id, $normalized, true)) {
                $normalized[] = $id;
            }
        }

        return $normalized;
    }

    private function resolveFrontTranslation(string $source, string $domainTranslation): string
    {
        try {
            $legacyTranslation = Translate::getModuleTranslation($this, $source, $this->name, null, false, null, false, false);
            if (is_string($legacyTranslation) && trim($legacyTranslation) !== '' && $legacyTranslation !== $source) {
                return $legacyTranslation;
            }
        } catch (Throwable $e) {
            // Keep the Symfony-domain translation as the reliable fallback.
        }

        return $this->fallbackTranslatedText($domainTranslation, $source);
    }

    private function fallbackTranslatedText(string $translated, string $fallback): string
    {
        $trimmed = trim((string) $translated);
        if ($trimmed === '') {
            return $fallback;
        }

        return (string) $translated;
    }

    private function getAdminPresentation(): CciCategoryGridAdminPresentation
    {
        return $this->adminPresentation ??= new CciCategoryGridAdminPresentation($this, $this->context);
    }
}
