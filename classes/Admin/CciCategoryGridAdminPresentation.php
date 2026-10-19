<?php

/** Back-office presentation; installation, hooks and business rules stay in the module. */
if (!defined('_PS_VERSION_')) {
    exit;
}

final class CciCategoryGridAdminPresentation
{
    public function __construct(
        private readonly Module $module,
        private readonly Context $context,
    ) {
    }

    public function renderHeroSection()
    {
        $heroImagePath = $this->module->getPathUri() . 'views/img/cci-hello.png';
        $heroStyle = '';

        if (is_file(_PS_MODULE_DIR_ . $this->module->name . '/views/img/cci-hello.png')) {
            $heroStyle = ' style="--cci-hero-image: url(\'' . $this->escapeHtml($heroImagePath) . '\')"';
        }

        return '<div class="cci-card cci-hero"' . $heroStyle . '>'
          . '<div class="cci-card-inner">'
          . '<div class="cci-hero-content">'
          . '<div class="cci-title-row">'
          . '<span class="cci-title"><img class="cci-product-logo" src="' . $this->module->getPathUri() . 'views/img/cci-category-grid-logo.png?v=' . $this->getAssetVersion($this->module->getLocalPath() . 'views/img/cci-category-grid-logo.png') . '" alt="' . $this->escapeHtml($this->module->displayName) . '" width="200" height="65"></span>'
          . '<span class="cci-version">v' . $this->escapeHtml($this->module->version) . '</span>'
          . '</div>'
          . '<p class="cci-subtitle">'
          . $this->escapeHtml($this->module->getTranslator()->trans('Display selected categories in a responsive front office grid and control image, submenu and category options in one place.', [], 'Modules.CciCategorygrid.Admin'))
          . '</p>'
          . '</div>'
          . '</div>'
          . '</div>';
    }

    public function renderBrandPanel()
    {
        $html = '<aside class="cci-brand-panel">';
        $html .= '<div class="cci-brand-card">';
        $html .= '<div class="cci-brand-title">' . $this->escapeHtml($this->module->getTranslator()->trans('About us', [], 'Modules.CciCategorygrid.Admin')) . '</div>';
        $html .= '<div class="cci-brand-logos">';
        $html .= '<img src="' . $this->module->getPathUri() . 'views/img/cool-cat-ideas-logo.svg" alt="Cool Cat Ideas">';
        $html .= '<img src="' . $this->module->getPathUri() . 'views/img/teastudio-logo.png" alt="teastudio">';
        $html .= '</div>';
        $html .= '<p class="cci-brand-text">' . $this->escapeHtml($this->module->getTranslator()->trans('This module is only a fraction of what we do. We deliver custom IT solutions that grow with your business. We ensure complete code optimization and security.', [], 'Modules.CciCategorygrid.Admin')) . '</p>';
        $html .= '<div class="cci-brand-heading">' . $this->escapeHtml($this->module->getTranslator()->trans('We implement and develop:', [], 'Modules.CciCategorygrid.Admin')) . '</div>';
        $html .= '<ul class="cci-check-list">';
        $html .= '<li><span class="cci-feature-text">' . $this->renderBrandFeature('Fast e-commerce on %highlight% (from implementations to custom modules)', 'PrestaShop') . '</span></li>';
        $html .= '<li><span class="cci-feature-text">' . $this->renderBrandFeature('Flexible and modern %highlight% websites', 'WordPress') . '</span></li>';
        $html .= '<li><span class="cci-feature-text">' . $this->renderBrandFeature('Scalable web applications in %highlight%', 'Next.js') . '</span></li>';
        $html .= '<li><span class="cci-feature-text">' . $this->renderBrandFeature('Process automation and %highlight%', 'API integrations') . '</span></li>';
        $html .= '</ul>';
        $html .= '<a class="cci-btn-primary" href="https://coolcatideas.com" target="_blank" rel="noopener noreferrer">' . $this->escapeHtml($this->module->getTranslator()->trans('See our offer', [], 'Modules.CciCategorygrid.Admin')) . '</a>';
        $html .= '</div>';
        $html .= '</aside>';

        return $html;
    }

    private function renderBrandFeature($source, $highlight)
    {
        $translated = $this->escapeHtml($this->module->getTranslator()->trans($source, [], 'Modules.CciCategorygrid.Admin'));
        $translatedHighlight = $this->escapeHtml($this->module->getTranslator()->trans($highlight, [], 'Modules.CciCategorygrid.Admin'));

        return str_replace('%highlight%', '<strong>' . $translatedHighlight . '</strong>', $translated);
    }

    private function getAssetVersion(string $filePath): string
    {
        return (string) (@filemtime($filePath) ?: $this->module->version);
    }

    private function escapeHtml($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
