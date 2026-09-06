<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$controller = (string) file_get_contents($root . '/controllers/admin/AdminCciCategoryGridController.php');
$module = (string) file_get_contents($root . '/cci_categorygrid.php');

if (strpos($controller, '$this->module->getContent()') === false
    || strpos($controller, 'redirectAdmin(') !== false
    || strpos($module, "getAdminLink(self::ADMIN_TAB_CLASS, false)") === false
    || strpos($module, "getAdminTokenLite(self::ADMIN_TAB_CLASS)") === false) {
    fwrite(STDERR, "FAIL: CCI Category Grid must render and save under its visible controller.\n");
    exit(1);
}

echo "CCI Category Grid admin menu context smoke test passed.\n";
