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
      $form->addElement('checkbox', 'is_recur', ts('Recurring Contributions'), NULL,
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
    if ($form->_values['event']['is_monetary'] && $form->_values['event']['is_recur']) {
      CRM_Eventinstallment_Utils::buildRecurForm($form);
    }
  }
  elseif (in_array($formName, ['CRM_Event_Form_Registration_Confirm', 'CRM_Event_Form_Registration_ThankYou'])) {
    $session = CRM_Core_Session::singleton();
    $params = $form->getVar('_params');
    $totalAmount = $form->getVar('_totalAmount');

    if (!empty($params['0']['is_recur'])) {
      CRM_Core_Region::instance('page-body')->add(['template' => 'CRM/Eventinstallment/SummaryBlock.tpl']);
      $template = CRM_Core_Smarty::singleton();
      $template->assign('is_recur', TRUE);
      $template->assign('frequency_interval', $params['0']['frequency_interval']);
      $template->assign('frequency_unit', $params['0']['frequency_unit']);
      $template->assign('installments', $params['0']['installments']);
      $totalAmount = $totalAmount;
      $installmentAmount = $totalAmount / $params['0']['installments'];
      $installmentAmount = CRM_Utils_Money::format($installmentAmount);
      $template->assign('installmentAmount', $installmentAmount);
    }

    if ($partialPaymentAmount = $session->get('partialPaymentAmount', 'partialPayment')) {
      $params = $form->getVar('_params');
      $session->set('partial_payment_total', $params['0']['amount'], 'partialPayment');
      $params['0']['amount'] = $partialPaymentAmount;
      $form->setVar('_params', $params);
      $form->setVar('_totalAmount', $partialPaymentAmount);
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
}

/**
 * Implements hook_civicrm_postProcess().
 */
function eventinstallment_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Event_Form_ManageEvent_Fee') {
    $submit = $form->getVar('_submitValues');
    if (!empty($form->getVar('_id'))) {
      $eventID = $form->getVar('_id');
      $submit['recur_frequency_unit'] = implode(CRM_Core_DAO::VALUE_SEPARATOR, array_keys($submit['recur_frequency_unit']));
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
}


function eventinstallment_civicrm_eventinstallment_pre(&$value, &$form, $contactID) {
  CRM_Eventinstallment_Utils::contributionRecur($value, $form, $contactID);
}


function eventinstallment_civicrm_pre($op, $objectName, $objectId, &$objectRef ) {
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
}
