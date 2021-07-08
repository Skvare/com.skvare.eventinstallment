<?php

use CRM_Eventinstallment_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Eventinstallment_Form_Setting extends CRM_Core_Form {
  const NUM_DISCOUNT = 6;
  /**
   * The id of the contribution page that we are processing.
   *
   * @var int
   */
  public $_id;
  /**
   * Set variables up before form is built.
   *
   * @throws \CRM_Contribute_Exception_InactiveContributionPageException
   * @throws \Exception
   */
  public function preProcess() {
    // current contribution page id
    $this->_id = CRM_Utils_Request::retrieve('event_id', 'Positive', $this);
  }
  public function buildQuickForm() {


    $this->add('select', 'events_relationships', 'Relationshiop type',
      CRM_Eventinstallment_Utils::relationshipTypes(),
      TRUE, ['class' => 'crm-select2', 'multiple' => 'multiple', 'placeholder' => ts('- any -')]);

    $civicrmFields = CRM_Eventinstallment_Utils::getCiviCRMFields();
    $operators = ['' => ts('-operator-')] +
      CRM_Eventinstallment_Utils::getOperators();

    $attribute = ['class' => 'crm-select2', 'placeholder' => ts('- any -')];
    /*
    $this->add('text', "events_jcc_discount", ts('JCC Discount'), ['size' => 20]);
    $this->add('select', 'events_jcc_discount_type', ts('JCC Discount Type'),
      [
        1 => E::ts('Percent'),
        2 => E::ts('Fixed Amount'),
      ],
      FALSE, $attribute);
    */
    $groups = ['' => '-- select --'] + CRM_Core_PseudoConstant::nestedGroup();
    $this->add('select', 'events_specal_discount_group', ts('Special Discount'),
      $groups, FALSE, $attribute);
    $this->add('text', "events_specal_discount", ts('Specail Discount'), ['size' =>
      20]);
    $this->add('select', 'events_specal_discount_type', ts('JCC Discount Type'),
      [
        1 => E::ts('Percent'),
        2 => E::ts('Fixed Amount'),
      ],
      FALSE, $attribute);

    $this->add('select', 'events_financial_discount_group', ts('Financial assistance/discount'),
      $groups, FALSE, $attribute);

    $contributionPage = CRM_Contribute_PseudoConstant::contributionPage();
    $events = CRM_Event_BAO_Event::getEvents(1);
    $this->add('select', 'events_id', ts('Event Name'), ['' => ts('- select -')] + $events, TRUE, $attribute);
    $this->add('select', "events_jcc_field", "JCC Field Name",
      $civicrmFields, FALSE, ['class' => 'crm-select2', 'placeholder' => ts('- any -')]);

    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($this->_id);
    $this->assign('num_disount', self::NUM_DISCOUNT);
    if (!empty($defaults['events_id'])) {
      $priceSetId = CRM_Price_BAO_PriceSet::getFor('civicrm_event', $defaults['events_id']);
      $fields = CRM_Eventinstallment_Utils::getPriceSetsOptions($priceSetId);
      $this->assign('price_fields', $fields);
      foreach ($fields as $fieldID => $fieldTitle) {
        if (!is_numeric($fieldID)) {
          continue;
        }
        $this->add('text', "events_rule[$fieldID][regular]", "Default Fee", [], FALSE);

        for ($i = 1; $i <= self::NUM_DISCOUNT; $i++) {
          $this->add('text', "events_rule[$fieldID][$i][discount_name]", ts('Discount Name'), ['size' => 20, 'placeholder' => ts('Discount name')]);
          $this->add('datepicker', "events_rule[$fieldID][$i][discount_start_date]", ts('Discount Start Date'), [], FALSE, ['time' => TRUE]);
          $this->add('datepicker', "events_rule[$fieldID][$i][discount_end_date]", ts('Discount End Date'), [], FALSE, ['time' => TRUE]);

          $this->add('text', "events_rule[$fieldID][$i][child_1]", ts('Child 1'), ['size' => 5]);
          $this->add('text', "events_rule[$fieldID][$i][child_2]", ts('Child 2'), ['size' => 5]);
          $this->add('text', "events_rule[$fieldID][$i][child_3]", ts('Child 3'), ['size' => 5]);
          $this->add('text', "events_rule[$fieldID][$i][child_4]", ts('Child 4'), ['size' => 5]);

          $this->add('text', "events_rule[$fieldID][$i][child_jcc_1]", ts('JCC Child 1'), ['size' => 5]);
          $this->add('text', "events_rule[$fieldID][$i][child_jcc_2]", ts('JCC Child 2'), ['size' => 5]);
          $this->add('text', "events_rule[$fieldID][$i][child_jcc_3]", ts('JCC Child 3'), ['size' => 5]);
          $this->add('text', "events_rule[$fieldID][$i][child_jcc_4]", ts('JCC Child 4'), ['size' => 5]);

          $this->add('text', "events_rule[$fieldID][$i][sibling_1]", ts('Sibling 1'), ['size' => 5]);
          $this->add('text', "events_rule[$fieldID][$i][sibling_2]", ts('Sibling 2'), ['size' => 5]);
          $this->add('text', "events_rule[$fieldID][$i][sibling_3]", ts('Sibling 3'), ['size' => 5]);
          $this->add('text', "events_rule[$fieldID][$i][sibling_4]", ts('Sibling 4'), ['size' => 5]);
        }
      }
    }


    $this->addButtons([
      [
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ],
    ]);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());

    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    // use settings as defined in default domain
    $domainID = CRM_Core_Config::domainID();
    $settings = Civi::settings($domainID);
    $eventConfig = [];
    foreach ($values as $k => $v) {
      if (strpos($k, 'events') === 0) {
        $eventConfig[$k] = $v;
      }
    }
    $eventID = $eventConfig['events_id'];
    if (!empty($eventConfig)) {
      CRM_Eventinstallment_Utils::setSettingsConfig($eventConfig, $eventID);
    }
    if (empty($this->_id)) {
      CRM_Core_Session::setStatus(E::ts('Setting updated successfully, now add discount'));
      $session = CRM_Core_Session::singleton();
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/event/setting', "reset=1&event_id={$eventID}"));
    }
    CRM_Core_Session::setStatus(E::ts('Setting updated successfully'));
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons". These
    // items don't have labels. We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }

    return $elementNames;
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  public function setDefaultValues() {
    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($this->_id);

    return $defaults;
  }

}
