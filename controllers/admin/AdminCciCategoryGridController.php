<?php

if (!defined('_PS_VERSION_')) {
  exit;
}

class AdminCciCategoryGridController extends ModuleAdminController
{
  public function __construct()
  {
    $this->bootstrap = true;
    parent::__construct();
    $this->meta_title = $this->module->displayName;
  }

  public function initContent()
  {
    $this->content = $this->module->getContent();
    parent::initContent();
  }
}
