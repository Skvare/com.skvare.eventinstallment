<?php

require_once 'eventinstallment.civix.php';
// phpcs:disable
use CRM_Eventinstallment_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function eventinstallment_civicrm_config(&$config) {
  _eventinstallment_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function eventinstallment_civicrm_xmlMenu(&$files) {
  _eventinstallment_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function eventinstallment_civicrm_install() {
  _eventinstallment_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function eventinstallment_civicrm_postInstall() {
  _eventinstallment_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function eventinstallment_civicrm_uninstall() {
  _eventinstallment_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function eventinstallment_civicrm_enable() {
  _eventinstallment_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function eventinstallment_civicrm_disable() {
  _eventinstallment_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function eventinstallment_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _eventinstallment_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function eventinstallment_civicrm_managed(&$entities) {
  _eventinstallment_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function eventinstallment_civicrm_caseTypes(&$caseTypes) {
  _eventinstallment_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function eventinstallment_civicrm_angularModules(&$angularModules) {
  _eventinstallment_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function eventinstallment_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _eventinstallment_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_thems().
 */
function eventinstallment_civicrm_themes(&$themes) {
  _eventinstallment_civix_civicrm_themes($themes);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function eventinstallment_civicrm_entityTypes(&$entityTypes) {
  $entityTypes['CRM_Event_DAO_Event']['fields_callback'][]
    = function ($class, &$fields) {
    $fields['is_recur'] = [
      'name' => 'is_recur',
      'type' => CRM_Utils_Type::T_BOOLEAN,
      'title' => E::ts('Is Recurring'),
      'description' => E::ts('if true - allows recurring event payment.'),
      'where' => 'civicrm_event.is_recur',
      'default' => '0',
      'table_name' => 'civicrm_event',
      'entity' => 'Event',
      'bao' => 'CRM_Event_BAO_Event',
      'localizable' => 0,
      'add' => '5.35',
    ];

    $fields['recur_frequency_unit'] = [
      'name' => 'recur_frequency_unit',
      'type' => CRM_Utils_Type::T_STRING,
      'title' => E::ts('Recurring Frequency'),
      'description' => E::ts('Supported recurring frequency units.'),
      'maxlength' => 128,
      'size' => CRM_Utils_Type::HUGE,
      'where' => 'civicrm_event.recur_frequency_unit',
      'table_name' => 'civicrm_event',
      'entity' => 'Event',
      'bao' => 'CRM_Event_BAO_Event',
      'localizable' => 0,
      'serialize' => CRM_Core_DAO::SERIALIZE_SEPARATOR_TRIMMED,
      'html' => [
        'type' => 'Select',
      ],
      'pseudoconstant' => [
        'optionGroupName' => 'recur_frequency_units',
        'keyColumn' => 'name',
        'optionEditPath' => 'civicrm/admin/options/recur_frequency_units',
      ],
      'add' => '5.35',
    ];

    $fields['is_recur_interval'] = [
      'name' => 'is_recur_interval',
      'type' => CRM_Utils_Type::T_BOOLEAN,
      'title' => E::ts('Support Recurring Intervals'),
      'description' => E::ts('if true - supports recurring intervals'),
      'where' => 'civicrm_event.is_recur_interval',
      'default' => '0',
      'table_name' => 'civicrm_event',
      'entity' => 'Event',
      'bao' => 'CRM_Event_BAO_Event',
      'localizable' => 0,
      'add' => '5.35',
    ];

    $fields['is_recur_installments'] = [
      'name' => 'is_recur_installments',
      'type' => CRM_Utils_Type::T_BOOLEAN,
      'title' => E::ts('Recurring Installments?'),
      'description' => E::ts('if true - asks user for recurring installments'),
      'where' => 'civicrm_event.is_recur_installments',
      'default' => '0',
      'table_name' => 'civicrm_event',
      'entity' => 'Event',
      'bao' => 'CRM_Event_BAO_Event',
      'localizable' => 0,
      'add' => '5.35',
    ];
  };
}


function eventinstallment_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Event_Form_ManageEvent_Fee') {
    $paymentProcessors = CRM_Financial_BAO_PaymentProcessor::getAllPaymentProcessors('live');
    $recurringPaymentProcessor = [];

    if (!empty($paymentProcessors)) {
      foreach ($paymentProcessors as $id => $processor) {
        if (!empty($processor['is_recur'])) {
          $recurringPaymentProcessor[] = $id;
        }
      }
    }
    if (!empty($recurringPaymentProcessor)) {
      if (count($recurringPaymentProcessor)) {
        $form->assign('recurringPaymentProcessor', $recurringPaymentProcessor);
      }
      $form->addElement('checkbox', 'is_recur', ts('Enable Installment'),
        NULL,
        ['onclick' => "showHideByValue('is_recur',true,'recurFields','table-row','radio',false);"]
      );
      $form->addCheckBox('recur_frequency_unit', ts('Supported recurring units'),
        CRM_Core_OptionGroup::values('recur_frequency_units', FALSE, FALSE, TRUE),
        NULL, NULL, NULL, NULL,
        ['&nbsp;&nbsp;', '&nbsp;&nbsp;', '&nbsp;&nbsp;', '<br/>'], TRUE
      );
      $form->addElement('checkbox', 'is_recur_interval', ts('Support recurring intervals'));
      $form->addElement('checkbox', 'is_recur_installments', ts('Offer installments'));
      $params = ['id' => $form->getVar('_id')];
      CRM_Event_BAO_Event::retrieve($params, $defaults);
      if (!empty($defaults['recur_frequency_unit'])) {
        $defaultsLocal = [];
        $defaultsLocal['recur_frequency_unit'] = array_fill_keys(explode(CRM_Core_DAO::VALUE_SEPARATOR, $defaults['recur_frequency_unit']), '1');
        $form->setDefaults($defaultsLocal);
      }
    }
  }
  elseif ($formName == 'CRM_Event_Form_Registration_Register') {
    $eid = $form->getVar('_eventId');
    if ($form->_values['event']['is_monetary'] && $form->_values['event']['is_recur']) {
      CRM_Eventinstallment_Utils::buildRecurForm($form);
    }
    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($eid);
    $session = CRM_Core_Session::singleton();
    if (!in_array($eid, (array)$defaults['events_id'])) {
      $session->set('is_jcc_member', FALSE);

      return;
    }
    $currentContactID = $form->getLoggedInUserContactID();

    CRM_Eventinstallment_Utils::_add_reload_textfield($form);

    $relatedContacts = CRM_Eventinstallment_Utils::relatedContactsListing($form);
    //echo '<pre>'; print_r($relatedContacts); echo '</pre>';
    $isPaid = TRUE;
    if (!empty($defaults['events_group_contact'])) {
      if ($relatedContacts[$currentContactID]['is_paid']) {
        $isPaid = TRUE;
      }
      else {
        $isPaid = FALSE;
        $relatedContacts[$currentContactID]['explanation'] = 'Membership Fee not paid';
      }
    }
    foreach ($relatedContacts as $contactID => $contact) {
      if ($contact['is_parent']) {
        $attribute = [];
        if ($contactID == $currentContactID) {
          $attribute = ['class' => 'currentUser'];
        }
        $element = $form->add('checkbox', "contacts_parent_{$contactID}",
          $contact['display_name'], NULL, FALSE, $attribute);
        if ($isPaid && !$contact['skip_registration'] && $contactID == $currentContactID) {
          $setDefaultForParent = ["contacts_parent_{$contactID}" => 1];
          $form->setDefaults($setDefaultForParent);
        }
      }
      else {
        $element = $form->add('checkbox', "contacts_child_{$contactID}", $contact['display_name']);
      }
      if ($contact['skip_registration'] || !$isPaid) {
        $element->freeze();
      }
    }
    $form->assign('relatedContacts', $relatedContacts);
    $isJccMember = FALSE;
    if (!empty($defaults['events_jcc_field']) && !empty($relatedContacts[$currentContactID][$defaults['events_jcc_field']])) {
      $isJccMember = TRUE;
    }
    $session->set('is_jcc_member', $isJccMember);
    $form->assign('currentContactID', $currentContactID);
    if (CRM_Utils_System::isUserLoggedIn()) {
      CRM_Core_Region::instance('page-body')->add(['template' => 'CRM/Eventinstallment/ContactListing.tpl']);
    }
  }
  elseif($formName == 'CRM_Event_Form_Registration_AdditionalParticipant') {
    $eid = $form->getVar('_eventId');
    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($eid);
    if (!in_array($eid, (array)$defaults['events_id'])) {
      return;
    }
    if (!$form->_values['event']['is_monetary']) {
      [$finalContactList, $childContacts, $parentContacts] = CRM_Eventinstallment_Utils::contactSequenceForRegistration($form);
      [$dontCare, $additionalPageNumber] = explode('_', $form->getVar('_name'));
      $contactID = $finalContactList[$additionalPageNumber];
      $childNumber = 0;
      if (in_array($contactID, $childContacts)) {
        $childNumber = CRM_Utils_Array::key($contactID, $childContacts);
      }
      $_params = $form->get('params');
      $_name = $form->getVar('_name');
      $participantNo = substr($_name, 12);
      $participantCnt = $participantNo;
      $participantTot = $_params[0]['additional_participants'];
      CRM_Utils_System::setTitle(ts('Register Child %1 of %2', [1 => $participantCnt, 2 => $participantTot]));
    }
    [$finalContactList, $childContacts, $parentContacts] = CRM_Eventinstallment_Utils::contactSequenceForRegistration($form);
    [$dontCare, $additionalPageNumber] = explode('_', $form->getVar('_name'));
    $contactID = $finalContactList[$additionalPageNumber];
    $data = CRM_Eventinstallment_Utils::getContactData(array_keys($form->_fields), $contactID);
    $form->setDefaults($data);
  }
  elseif (in_array($formName, ['CRM_Event_Form_Registration_Confirm', 'CRM_Event_Form_Registration_ThankYou'])) {
    $session = CRM_Core_Session::singleton();
    if ($formName == 'CRM_Event_Form_Registration_Confirm') {
      CRM_Eventinstallment_Utils::getAdditionalDiscount($form);
    }
    else {
      CRM_Eventinstallment_Utils::getAdditionalDiscount($form, TRUE);
      $eid = $form->getVar('_eventId');
      $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($eid);
      if (!in_array($eid, (array)$defaults['events_id'])) {
        return;
      }
      $session = CRM_Core_Session::singleton();
      if ($session->get('parents_not_allowed') && $form->getVar('_participantId')) {
        $form->getVar('_participantId');
        $result = civicrm_api3('ParticipantStatusType', 'getvalue', [
          'return' => "id",
          'name' => "not_attending",
        ]);
        CRM_Core_DAO::setFieldValue('CRM_Event_DAO_Participant', $form->getVar('_participantId'), 'status_id', $result);
      }
    }

    $params = $form->getVar('_params');
    $totalAmount = $form->getVar('_totalAmount');
    //$_amount = $form->getVar('_amount');
    if (!empty($params['0']['is_recur'])) {
      $template = CRM_Core_Smarty::singleton();
      $template->assign('is_recur', TRUE);
      $template->assign('frequency_interval', $params['0']['frequency_interval']);
      $template->assign('frequency_unit', $params['0']['frequency_unit']);
      $template->assign('installments', $params['0']['installments']);
      $totalAmount = $totalAmount;
      $installmentAmount = $totalAmount / $params['0']['installments'];
      $installmentAmount = CRM_Utils_Money::format($installmentAmount);
      $template->assign('installmentAmount', $installmentAmount);
      CRM_Core_Region::instance('page-body')->add(['template' => 'CRM/Eventinstallment/SummaryBlock.tpl']);
    }
    // To avoid confusion, changing removing parent name as first participant
    // and changing the labels too.
    if ($session->get('parents_not_allowed')) {
      $template = CRM_Core_Smarty::singleton();
      $part = $template->get_template_vars('part');
      $part[0]['info'] = ' ( Parent will be register as Non Attending Participant.)';
      $template->assign('part', $part);
      CRM_Core_Region::instance('page-body')->add(['template' => 'CRM/Eventinstallment/LineItem.tpl']);
    }
  }
}

function eventinstallment_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Event_Form_ManageEvent_Fee') {
    if (isset($fields['is_recur'])) {
      if (empty($fields['recur_frequency_unit'])) {
        $errors['recur_frequency_unit'] = ts('At least one recurring frequency option needs to be checked.');
      }
    }
  }
  elseif ($formName == 'CRM_Event_Form_Registration_Register') {
    $eventId = $form->getVar('_eventId');
    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($eventId);
    if (!in_array($eventId, (array)$defaults['events_id'])) {
      return;
    }
    $parents_can_register = CRM_Eventinstallment_Utils::canParentRegisterforEvent($eventId);
    $currentContactID = $form->getLoggedInUserContactID();
    $childContacts = $parentContact = [];
    foreach ($fields as $k => $v) {
      if (strpos($k, 'contacts_child_') === 0) {
        [, , $cid] = explode('_', $k);
        $childContacts[$cid] = $cid;
      }
      elseif (strpos($k, 'contacts_parent_') === 0) {
        [, , $cid] = explode('_', $k);
        $parentContact[$cid] = $cid;
      }
    }

    if (empty($childContacts)) {
      $errors['additional_participants'] = ts('Select at least one child');
    }
    if (!$parents_can_register || ($parents_can_register && empty($parentContact[$currentContactID]))) {
      if (!$form->_values['event']['is_monetary']) {
        return;
      }
      $session = CRM_Core_Session::singleton();
      $session->set('event_skip_main_parent', TRUE);
      foreach ($form->_priceSet['fields'] as $fid => $val) {
        $form->setElementError('price_' . $fid, NULL);
        $fields['price_' . $fid] = 0;
      }
      $form->_lineItem = [];
      $form->setElementError('_qf_default', NULL);
    }
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function eventinstallment_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Event_Form_ManageEvent_Fee') {
    $submit = $form->getVar('_submitValues');
    if (!empty($form->getVar('_id'))) {
      $eventID = $form->getVar('_id');
      $submit['recur_frequency_unit'] = (is_array($submit['recur_frequency_unit']) && !empty($submit['recur_frequency_unit'])) ? implode(CRM_Core_DAO::VALUE_SEPARATOR, array_keys($submit['recur_frequency_unit'])) : '';
      $submit['recur_frequency_unit'] = $submit['recur_frequency_unit'] ?? '';
      $submit['is_recur_interval'] = $submit['is_recur_interval'] ?? 0;
      $submit['is_recur_installments'] = $submit['is_recur_installments'] ?? 0;
      $submit['is_recur'] = $submit['is_recur'] ?? 0;
      $param = [
        1 => [$submit['recur_frequency_unit'], 'String'],
        2 => [$submit['is_recur_interval'], 'Boolean'],
        3 => [$submit['is_recur_installments'], 'Boolean'],
        4 => [$submit['is_recur'], 'Boolean']
      ];

      $query = "
      UPDATE civicrm_event
        SET recur_frequency_unit = %1,
        is_recur_interval = %2,
        is_recur_installments = %3,
        is_recur = %4
      WHERE id = $eventID
      ";
      CRM_Core_DAO::executeQuery($query, $param);
    }
  }
  elseif ($formName == "CRM_Event_Form_Registration_Register") {
    $eid = $form->getVar('_eventId');
    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($eid);
    if (!in_array($eid, (array)$defaults['events_id'])) {
      return;
    }
    $currentContactID = $form->getLoggedInUserContactID();

    $parents_can_register = CRM_Eventinstallment_Utils::canParentRegisterforEvent($eid);
    $session = CRM_Core_Session::singleton();
    if (!$parents_can_register) {
      $session = CRM_Core_Session::singleton();
      $session->set('parents_not_allowed', TRUE);
      $session->set('parents_not_allowed_contact_id', $currentContactID);
    }
    elseif (!empty($form->_submitValues) &&
      !empty($form->_submitValues['contacts_parent_' . $currentContactID])) {
      $session->set('parents_not_allowed', FALSE);
    }
    else {
      $session->set('parents_not_allowed', TRUE);
    }

  }
  elseif (FALSE && $formName == "CRM_Event_Form_Registration_Confirm") {
    $eid = $form->getVar('_eventId');
    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($eid);
    if (!in_array($eid, (array)$defaults['events_id'])) {
      return;
    }
    $session = CRM_Core_Session::singleton();
    if ($session->get('parents_not_allowed') && $form->getVar('_participantId')) {
      //$parentContactID = $session->get('parents_not_allowed_contact_id');
      $form->getVar('_participantId');
      $result = civicrm_api3('ParticipantStatusType', 'getvalue', [
        'return' => "id",
        'name' => "not_attending",
      ]);
      CRM_Core_DAO::setFieldValue('CRM_Event_DAO_Participant', $form->getVar('_participantId'), 'status_id', $result);
    }
  }
}


function eventinstallment_civicrm_eventinstallment_pre(&$value, &$form, $contactID) {
  CRM_Eventinstallment_Utils::contributionRecur($value, $form, $contactID);
}


function eventinstallment_civicrm_pre($op, $objectName, $objectId, &$objectRef ) {
  /*
  if ($objectName == 'Participant' && $op != 'delete') {
    $additinalParticipant = FALSE;
    if (!empty($objectId)) {
      $objectRef['skipLineItem'] = 1;
    }
    if (empty($objectId) && !empty($objectRef['registered_by_id'])) {
      $objectId = $objectRef['registered_by_id'];
      $additinalParticipant = TRUE;
    }
    elseif (!empty($objectId) && !empty($objectRef['registered_by_id'])) {
      $objectId = $objectRef['registered_by_id'];
      $additinalParticipant = TRUE;
    }
    // $objectId always be main participant record.
    // Check This participant record belong to recurring payment.
    if ($objectId && CRM_Eventinstallment_Utils::isRecurring($objectId)) {

      // then get total amount till date with completed status.
      $amountPaid = CRM_Eventinstallment_Utils::amountPaid($objectId);

      // get actaul event fee including additional participant fee
      $actualParentEventFee = CRM_Eventinstallment_Utils::totalEventFee($objectId);

      $participantStatuses = CRM_Event_PseudoConstant::participantStatus();
      $newStatus = array_search('Registered', $participantStatuses);
      // if amount paid is less than total event fee then , status of
      // participant must be Partially paid
      if ($amountPaid < $actualParentEventFee) {
        $newStatus = array_search('Partially paid', $participantStatuses);
      }
      $objectRef['participant_status_id'] = $objectRef['status_id'] = $newStatus;
    }
  }
  */
}

function eventinstallment_civicrm_buildAmount($pageType, &$form, &$amounts) {
  if ((!$form->getVar('_action')
      || ($form->getVar('_action') & CRM_Core_Action::PREVIEW)
      || ($form->getVar('_action') & CRM_Core_Action::ADD)
      || ($form->getVar('_action') & CRM_Core_Action::UPDATE)
    )
    && !empty($amounts) && is_array($amounts) && ($pageType == 'event')
  ) {

    $formName = get_class($form);
    if (!in_array(get_class($form), [
      'CRM_Event_Form_Registration_Register',
      'CRM_Event_Form_Registration_AdditionalParticipant',
    ])
    ) {
      return;
    }
    $eid = $form->getVar('_eventId');
    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($eid);
    if (!in_array($eid, (array)$defaults['events_id']) || empty($defaults['events_rule'])) {
      return;
    }

    $currentContactID = $form->getLoggedInUserContactID();

    if ($formName == 'CRM_Event_Form_Registration_AdditionalParticipant') {
      [$finalContactList, $childContacts, $parentContacts] = CRM_Eventinstallment_Utils::contactSequenceForRegistration($form);
      [$dontCare, $additionalPageNumber] = explode('_', $form->getVar('_name'));
      $contactID = $finalContactList[$additionalPageNumber];
      $childNumber = 0;
      if (in_array($contactID, $childContacts)) {
        $childNumber = CRM_Utils_Array::key($contactID, $childContacts);
      }
      $_params = $form->get('params');
      $_name = $form->getVar('_name');
      $participantNo = substr($_name, 12);
      $participantCnt = $participantNo;
      $participantTot = $_params[0]['additional_participants'];
      CRM_Utils_System::setTitle(ts('Register Child %1 of %2', [1 => $participantCnt, 2 => $participantTot]));

      /*
      echo '<pre>$contactID : '; print_r($contactID); echo '</pre>';
      echo '<pre>$childNumber : '; print_r($childNumber); echo '</pre>';
      echo '<pre>$childContacts : '; print_r($childContacts); echo '</pre>';
      echo '<pre>$finalContactList : '; print_r($finalContactList); echo '</pre>';
      */
    }
    else {
      $childNumber = 0;
      $parents_can_register = CRM_Eventinstallment_Utils::canParentRegisterforEvent($eid);
      if (!$parents_can_register) {
        $amount = 0;
      }
    }

    $psid = $form->get('priceSetId');
    $getPriceSetsInfo = CRM_Eventinstallment_Utils::getPriceSetsInfo($psid);

    $session = CRM_Core_Session::singleton();
    $isJccMember = FALSE;
    if($session->get('is_jcc_member')) {
      $isJccMember = TRUE;
    }
    //echo '<pre>$childNumber - '; print_r($childNumber); echo '</pre>';
    $originalAmounts = $amounts;
    foreach ($amounts as $fee_id => &$fee) {
      if (!is_array($fee['options'])) {
        continue;
      }
      foreach ($fee['options'] as $option_id => &$option) {
        if (array_key_exists($option_id, $getPriceSetsInfo)) {
          [$defaultFee, $sellAmount, $discountAmount] =
            CRM_Eventinstallment_Utils::getDiscountAmount($eid, $option_id,
              $childNumber, $isJccMember);
          if (empty($sellAmount)) {
            continue;
          }
          if ($childNumber == 0 && isset($parents_can_register) && !$parents_can_register) {
            $sellAmount = 0;
          }
          elseif ($formName == 'CRM_Event_Form_Registration_Register' &&
            !empty($form->_submitValues) &&
            empty($form->_submitValues['contacts_parent_' . $currentContactID])) {
            $sellAmount = 0;
            //$session = CRM_Core_Session::singleton();
            //$session->set('parents_not_allowed', TRUE);
          }
          elseif ($formName == 'CRM_Event_Form_Registration_Register' &&
            !empty($form->_submitValues) &&
            !empty($form->_submitValues['contacts_parent_' . $currentContactID])) {
            //$session = CRM_Core_Session::singleton();
            //$session->set('parents_not_allowed', FALSE);
          }
          /*
          $data = 'Label : ' . $option['label'] . ' -  ' . $defaultFee . ' -> ' . $sellAmount . ' : discount Amount :' . $discountAmount;
          echo '<pre>';echo $data;echo '</pre>';
          */

          $option['amount'] = $sellAmount;

          // Re-calculate VAT/Sales TAX on discounted amount.
          if (array_key_exists('tax_amount', $originalAmounts[$fee_id]['options'][$option_id]) &&
            array_key_exists('tax_rate', $originalAmounts[$fee_id]['options'][$option_id])
          ) {
            $recalculateTaxAmount = CRM_Contribute_BAO_Contribution_Utils::calculateTaxAmount($amount, $originalAmounts[$fee_id]['options'][$option_id]['tax_rate']);
            if (!empty($recalculateTaxAmount)) {
              $option['tax_amount'] = round($recalculateTaxAmount['tax_amount'], 2);
            }
          }
        }
      }
    }
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function eventinstallment_civicrm_navigationMenu(&$menu) {
  _eventinstallment_civix_insert_navigation_menu($menu, 'Administer/CiviEvent', [
    'label' => E::ts('Custom Event Signup Setting'),
    'name' => 'custom_event_signup_listing',
    'url' => 'civicrm/admin/event/customlisting',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ]);
  _eventinstallment_civix_navigationMenu($menu);
}

function eventinstallment_civicrm_alterTemplateFile($formName, $form, $context, &$tplName) {
  if ($formName == 'CRM_Event_Form_Registration_Register' && $form->getVar('_eventId')) {
    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($form->getVar('_eventId'));
    if (in_array($form->getVar('_eventId'), (array)$defaults['events_id'])) {
      if (!CRM_Utils_System::isUserLoggedIn()) {
        $config = CRM_Core_Config::singleton();
        $destination = $config->userSystem->getLoginDestination($form);
        $loginURL = $config->userSystem->getLoginURL($destination);
        $template = CRM_Core_Smarty::singleton();
        $template->assign('loginURL', $loginURL);
        $tplName = 'AccessDenied.tpl';
      }
    }
  }
}
