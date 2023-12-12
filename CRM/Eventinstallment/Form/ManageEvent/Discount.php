<?php

use CRM_Eventinstallment_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Eventinstallment_Form_ManageEvent_Discount extends
  CRM_Event_Form_ManageEvent {
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
    parent::preProcess();
    // current contribution page id
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    if ($this->_id) {
      $this->_doneUrl = CRM_Utils_System::url(CRM_Utils_System::currentPath(), "action=update&reset=1&id={$this->_id}");
    }
  }
  public function buildQuickForm() {
    $groups = ['' => '-- select --'] + CRM_Core_PseudoConstant::nestedGroup();

    $this->add('select', 'events_relationships', 'Relationship type',
      CRM_Eventinstallment_Utils::relationshipTypes(),
      TRUE, ['class' => 'crm-select2', 'multiple' => 'multiple', 'placeholder' => ts('- any -')]);

    $civicrmFields = CRM_Eventinstallment_Utils::getCiviCRMFields();
    $operators = ['' => ts('-operator-')] +
      CRM_Eventinstallment_Utils::getOperators();

    $attribute = ['class' => 'crm-select2', 'placeholder' => ts('- any -')];

    $membershipTypes = CRM_Eventinstallment_Utils::membershipTypeCurrentDomain();
    $this->add('select', 'events_membership_types', 'Membership Type to Filter Contact',
      $membershipTypes, FALSE, $attribute + ['multiple' => 'multiple']);

    $this->add('select', 'events_group_contact', ts('Contact in this Group allowed to Register for Event.'),
      $groups, FALSE, $attribute + ['multiple' => 'multiple']);

    $this->add('select', 'events_special_discount_group', ts('Special Discount Group'),
      $groups, FALSE, $attribute);
    $this->add('text', "events_special_discount_amount", ts('Special Discount Amount'), ['size' =>
      20]);
    $this->add('select', 'events_special_discount_type', ts('Special Discount Type'),
      [
        1 => E::ts('Percent'),
        2 => E::ts('Fixed Amount'),
      ],
      FALSE, $attribute);

    $this->add('select', 'events_financial_discount_group', ts('Financial assistance/discount'),
      $groups, FALSE, $attribute);
    $this->add('select', "events_financial_discount_group_discount_amount",
      "Field for Amount",
      $civicrmFields, FALSE, ['class' => 'crm-select2', 'placeholder' => ts('- any -')]);
    $this->add('select', "events_financial_discount_group_discount_type", "Field for Discount Type",
      $civicrmFields, FALSE, ['class' => 'crm-select2', 'placeholder' => ts('- any -')]);

    $contributionPage = CRM_Contribute_PseudoConstant::contributionPage();
    $events = CRM_Event_BAO_Event::getEvents(1, NULL, TRUE, FALSE);
    $this->add('hidden', 'events_id');
    $this->add('select', "events_jcc_field", "JCC Field Name",
      $civicrmFields, FALSE, ['class' => 'crm-select2', 'placeholder' => ts('- any -')]);

    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($this->_id);
    $this->assign('num_disount', self::NUM_DISCOUNT);
    $this->setDefaults(['events_id' => $this->_id]);
    if (!empty($this->_id)) {
      $priceSetId = CRM_Price_BAO_PriceSet::getFor('civicrm_event', $this->_id);
      if ($priceSetId) {
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
    }
    /*
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ],
    ]);
    */
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
    $eventID = $this->_id;
    if (!empty($eventConfig)) {
      CRM_Eventinstallment_Utils::setSettingsConfig($eventConfig, $eventID);
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
