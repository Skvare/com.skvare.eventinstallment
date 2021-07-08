<?php
use CRM_Eventinstallment_ExtensionUtil as E;

class CRM_Eventinstallment_Page_Listing extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('Event Discount Configuration Listing'));
    $configList = CRM_Eventinstallment_Utils::getAllSettingConfig();
    // Example: Assign a variable for use in a template
    $this->assign('configList', $configList);

    parent::run();
  }
}
