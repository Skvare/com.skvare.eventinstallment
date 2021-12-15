<?php
use CRM_Eventinstallment_ExtensionUtil as E;

class CRM_Eventinstallment_Utils {

  /**
   * @return array
   */
  public static function relationshipTypes() {
    $result = civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'is_active' => 1,
      'options' => ['limit' => 0],
    ]);


    $relationshipTypes = [];
    foreach ($result['values'] as $type) {
      if ($type['label_a_b'] == $type['label_b_a']) {
        $relationshipTypes[$type['id']] = $type['label_a_b'];
      }
      else {
        $relationshipTypes[$type['id'] . '_a_b'] = $type['label_a_b'];
        $relationshipTypes[$type['id'] . '_b_a'] = $type['label_b_a'];
      }
    }

    return $relationshipTypes;
  }

  /**
   * Search builder operators.
   *
   * @return array
   */
  public static function getOperators() {
    return [
      '=' => '=',
      '!=' => '≠',
      '>' => '>',
      '<' => '<',
      '>=' => '≥',
      '<=' => '≤',
      '<=>' => ts('Between'),
      'IN' => ts('In'),
      'NOT IN' => ts('Not In'),
    ];
  }

  public static function canParentRegisterforEvent($eventId) {
    $result = civicrm_api3('Event', 'get', [
      'id' => $eventId,
    ]);
    $eventDetails = $result['values'][$eventId];

    try {
      $fid = civicrm_api3('CustomField', 'getvalue', [
        'custom_group_id' => 'Multireg',
        'name' => 'Parents_Can_Register',
        'return' => 'id',
      ]);
      $parents_can_register = !empty($eventDetails["custom_$fid"]);
    }
    catch (CiviCRM_API3_Exception $e) {
      $parents_can_register = FALSE;
    }

    return $parents_can_register;
  }

  /**
   * @param $form
   */
  public static function getAdditionalDiscount($form, $noCal = FALSE) {
    $session = CRM_Core_Session::singleton();
    $params = $form->getVar('_params');
    $totalAmount = $form->getVar('_totalAmount');
    $lineItem = $form->getVar('_lineItem');
    $_amount = $form->getVar('_amount');
    $_values = $form->getVar('_values');

    /*
    $resultContribution = civicrm_api3('PriceField', 'get', [
      'sequential' => 1,
      'price_set_id' => "default_contribution_amount",
      'api.PriceFieldValue.get' => [],
    ]);
    $priceFieldsContribution = reset($resultContribution['values']['0']['api.PriceFieldValue.get']['values']);
    */

    $currentContactID = $form->getLoggedInUserContactID();
    $eid = $form->getVar('_eventId');


    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($eid);


    $returnField = ["group"];
    if (!empty($defaults['events_financial_discount_group_discount_amount'])) {
      $returnField[] = $defaults['events_financial_discount_group_discount_amount'];
      $returnField[] = $defaults['events_financial_discount_group_discount_type'];
    }
    $contactResult = civicrm_api3('Contact', 'getsingle', [
      'return' => $returnField,
      'id' => $currentContactID,
    ]);
    $groupContact = [];
    if (!empty($contactResult['groups'])) {
      $groupContact = explode(',', $contactResult['groups']);
    }
    if (is_array($lineItem) && !array_key_exists('0', $lineItem)) {
      array_unshift($lineItem, []);
    }
    $originalTotalAmount = $totalAmount;
    if (!empty($defaults['events_financial_discount_group'])
      && !empty($defaults['events_financial_discount_group_discount_amount'])
      && !empty($defaults['events_financial_discount_group_discount_type'])
    ) {
      if (in_array($defaults['events_financial_discount_group'], $groupContact) &&
        !empty($contactResult[$defaults['events_financial_discount_group_discount_amount']]) &&
        !empty($contactResult[$defaults['events_financial_discount_group_discount_type']])
      ) {
        $type = 1;
        if ($contactResult[$defaults['events_financial_discount_group_discount_type']] == 'fixed_amount') {
          $type = 2;
        }
        [$newTotalAmount, $discountAmount, $newLabel] = self::_calc_discount
        ($originalTotalAmount, $contactResult[$defaults['events_financial_discount_group_discount_amount']], $type, 'Financial Assistant Discount');
        $item = [];
        $item['qty'] = 1;
        $item['financial_type_id'] = $_values['event']['financial_type_id'];
        //$item['price_field_id'] = $priceFieldsContribution['price_field_id'];
        //$item['price_field_value_id'] = $priceFieldsContribution['id'];
        $item['unit_price'] = $discountAmount;
        $item['line_total'] = $discountAmount;
        $item['label'] = $newLabel;
        $item['entity_table'] = "civicrm_contribution";
        $lineItem[0][] = $item;
        if (!$noCal) {
          $totalAmount = $totalAmount + $discountAmount;
        }
        $_amount[] = ['amount' => $discountAmount, 'label' => $newLabel];
      }
    }

    if (!empty($defaults['events_special_discount_group'])
      && !empty($defaults['events_special_discount_amount'])
      && !empty($defaults['events_special_discount_type'])
    ) {
      if (in_array($defaults['events_special_discount_group'], $groupContact)) {
        $type = $defaults['events_special_discount_type'];
        [$newTotalAmount, $discountAmount, $newLabel] = self::_calc_discount($originalTotalAmount, $defaults['events_special_discount_amount'], $type, 'Special Discount');
        $item = [];
        $item['qty'] = 1;
        $item['financial_type_id'] = $_values['event']['financial_type_id'];
        //$item['price_field_id'] = $priceFieldsContribution['price_field_id'];
        //$item['price_field_value_id'] = $priceFieldsContribution['id'];
        $item['unit_price'] = $discountAmount;
        $item['line_total'] = $discountAmount;
        $item['label'] = $newLabel;
        $item['entity_table'] = "civicrm_contribution";
        $lineItem[0][] = $item;
        if (!$noCal) {
          $totalAmount = $totalAmount + $discountAmount;
        }
        $_amount[] = ['amount' => $discountAmount, 'label' => $newLabel];
      }
    }

    if ($session->get('parents_not_allowed')) {
      if (empty($lineItem[0])) {
        unset($lineItem[0]);
      }
    }
    $form->setVar('_lineItem', $lineItem);
    $form->set('_lineItem', $lineItem);
    $form->assign('lineItem', $lineItem);
    $form->setVar('_amount', $_amount);
    $form->assign('amounts', $_amount);
    $form->setVar('_totalAmount', $totalAmount);
    $form->assign('totalAmount', $totalAmount);
    $form->set('totalAmount', $totalAmount);
  }

  /**
   * Calculate either a monetary or percentage discount.
   *
   * @param $amount
   * @param $discountAmount
   * @param int $type
   * @param string $label
   * @param string $currency
   * @return array
   */
  public static function _calc_discount($amount, $discountAmount, $type = 1, $label = '', $currency = 'USD') {
    if ($type == '2') {
      $newamount = CRM_Utils_Rule::cleanMoney($amount) - CRM_Utils_Rule::cleanMoney($discountAmount);
      $fmt_discount = CRM_Utils_Money::format($discountAmount, $currency);
      $newlabel = $label . " ({$fmt_discount})";
    }
    else {
      // Percentage
      $newamount = $amount - ($amount * ($discountAmount / 100));
      $newlabel = $label . " ({$discountAmount}%)";
    }

    $newamount = round($newamount, 2);
    // Return a formatted string for zero amount.
    // @see http://issues.civicrm.org/jira/browse/CRM-12278
    if ($newamount <= 0) {
      $newamount = '0.00';
    }
    $discountAmount = $newamount - $amount;

    return [$newamount, $discountAmount, $newlabel];
  }

  /**
   * @param $form
   * @return array
   */
  public static function relatedContactsListing($form) {
    $group_members = [];

    // Get logged in user Contact ID
    $userID = $form->getLoggedInUserContactID();
    $eventId = $form->getVar('_eventId');

    $result = civicrm_api3('Event', 'get', [
      'id' => $eventId,
    ]);
    $eventDetails = $result['values'][$eventId];

    $primary_contact_params = [
      'version' => '3',
      'id' => $userID,
    ];
    // Get all Contact Details for logged in user
    $civi_primary_contact = civicrm_api('Contact', 'getsingle', $primary_contact_params);
    $civi_primary_contact['display_name'] .= ' (you)';
    $group_members[$userID] = $civi_primary_contact;

    $defaults = CRM_Eventinstallment_Utils::getSettingsConfig($eventId);
    $relationships = $defaults['events_relationships'];
    $rab = [];
    $rba = [];

    // parents can only register for events that allow it
    $parents_can_register = self::canParentRegisterforEvent($eventId);

    foreach ($relationships as $r) {
      @ list($rType, $dir) = explode("_", $r, 2);
      if ($dir == NULL) {
        $rab[] = $rType;
        $rba[] = $rType;
      }
      elseif ($dir = "a_b") {
        $rab[] = $rType;
      }
      else {
        $rba[] = $rType;
      }
    }

    $contactIds = [$userID];
    if (!empty($rab)) {
      $relationshipsCurrentUserOnBSide = civicrm_api3('Relationship', 'get', [
        'return' => ["contact_id_a"],
        'contact_id_b' => "user_contact_id",
        'is_active' => TRUE,
        'relationship_type_id' => ['IN' => $rab]
      ]);
      foreach ($relationshipsCurrentUserOnBSide['values'] as $rel) {
        $contactIds[] = $rel['contact_id_a'];
      }
    }
    if (!empty($rba)) {
      $relationshipsCurrentUserOnASide = civicrm_api3('Relationship', 'get', [
        'return' => ["contact_id_b"],
        'contact_id_a' => "user_contact_id",
        'is_active' => TRUE,
        'relationship_type_id' => ['IN' => $rba]
      ]);
      foreach ($relationshipsCurrentUserOnASide['values'] as $rel) {
        $contactIds[] = $rel['contact_id_b'];
      }
    }

    //make it a unique list of contacts
    $contactIds = array_unique($contactIds);

    $returnField = ["display_name", "group"];
    if (!empty($defaults['events_jcc_field'])) {
      $returnField[] = $defaults['events_jcc_field'];
    }

    $spouse_of_id = civicrm_api3('RelationshipType', 'getvalue', [
      'name_a_b' => 'Spouse of',
      'return' => 'id',
    ]);
    $couple = [$userID, 0];

    // Get all related Contacts for this user
    foreach ($contactIds as $cid) {
      // only look for parent / child relationship
      try {
        $contactDataResult = civicrm_api("Contact", "get", [
            'return' => $returnField,
            'version' => 3,
            'id' => $cid,
            'is_deleted' => 0]
        );
        if (!empty($contactDataResult['values'])) {
          $group_members[$cid] = $contactDataResult['values'][$cid];
        }
        else {
          continue;
        }
      }
      catch (CiviCRM_API3_Exception $exception) {
        continue;
      }

      $group_members[$cid]['is_parent'] = FALSE;
      if (!empty($defaults['events_group_contact'])) {
        $groupContact = [];
        if (!empty($group_members[$cid]['groups'])) {
          $groupContact = explode(',', $group_members[$cid]['groups']);
        }
        $isGroupPresent = array_intersect($defaults['events_group_contact'], $groupContact);
        $group_members[$cid]['is_paid'] = FALSE;
        if (!empty($isGroupPresent)) {
          $group_members[$cid]['is_paid'] = TRUE;
        }
      }
      if ($userID == $cid) {
        $group_members[$cid]['display_name'] .= ' (you)';
        $group_members[$cid]['is_parent'] = TRUE;
      }
      else {
        $couple[1] = $cid;
        $count = civicrm_api3('Relationship', 'getcount', [
          'relationship_type_id' => $spouse_of_id,
          'is_active' => 1,
          'contact_id_a' => [
            'IN' => $couple
          ],
          'contact_id_b' => [
            'IN' => $couple
          ]
        ]);
        if ($count) {
          $group_members[$cid]['is_parent'] = TRUE;
        }
      }

      $membershipParams = [
        'contact_id' => $cid,
        //'membership_type_id' => "1",
        'status_id' => ['IN' => ["New", "Current"]],
        'options' => ['sort' => "end_date desc", 'limit' => 1],
      ];
      if (!empty($defaults['events_membership_types'])) {
        $membershipParams['membership_type_id'] = ['IN' => $defaults['events_membership_types']];
      }
      $resultMembership = civicrm_api3('Membership', 'get', $membershipParams);
      // For parent membership is not required, they can register for event
      // if its allowed through event custom setting.
      if (!empty($resultMembership['values']) || ($parents_can_register && $group_members[$cid]['is_parent'])) {
        $group_members[$cid]['membership'] = 'Yes';
        $group_members[$cid]['skip_registration'] = FALSE;
        $group_members[$cid]['explanation'] = '';
      }
      else {
        $group_members[$cid]['membership'] = 'No';
        $group_members[$cid]['skip_registration'] = TRUE;
        $group_members[$cid]['explanation'] = 'Active membership not found';
      }

    }

    foreach ($group_members as $cid => $contactDetails) {
      if ($contactDetails['is_parent']) {
        if (!$parents_can_register && array_key_exists($cid, $group_members)) {
          $group_members[$cid]['skip_registration'] = TRUE;
          $group_members[$cid]['explanation'] = 'Parents cannot register for this event';
        }
      }
    }

    return $group_members;
  }

  /**
   * Build elements to collect information for recurring contributions.
   *
   *
   * @param CRM_Core_Form $form
   */
  public static function buildRecurForm(&$form) {
    $attributes = CRM_Core_DAO::getAttribute('CRM_Contribute_DAO_ContributionRecur');
    $className = get_class($form);

    $form->assign('is_recur_interval', CRM_Utils_Array::value('is_recur_interval', $form->_values['event']));
    $form->assign('is_recur_installments', CRM_Utils_Array::value('is_recur_installments', $form->_values['event']));
    $paymentObject = $form->getVar('_paymentObject');
    $params = $form->_values['event'];
    $gotText = ts('Your recurring event fee will be processed automatically.');
    if ($params['is_recur_installments']) {
      $gotText .= ' ' . ts('You can specify the number of installments, or you can leave the number of installments blank if you want to make an open-ended commitment. In either case, you can choose to cancel at any time.');
    }
    if (!empty($params['is_email_receipt'])) {
      $gotText .= ' ' . ts('You will receive an email receipt for each recurring payment.');
    }
    if ($paymentObject) {
      $form->assign('recurringHelpText', $gotText);
    }

    $form->add('checkbox', 'is_recur', ts('Check this box if you prefer dividing this fee into multiple payments'), NULL);

    if (!empty($form->_values['event']['is_recur_interval'])) {
      $form->add('text', 'frequency_interval', ts('Every'), $attributes['frequency_interval'] + ['aria-label' => ts('Every')]);
      $form->addRule('frequency_interval', ts('Frequency must be a whole number (EXAMPLE: Every 1 months).'), 'integer');
    }
    else {
      // make sure frequency_interval is submitted as 1 if given no choice to user.
      $form->add('hidden', 'frequency_interval', 1);
    }

    $frUnits = $form->_values['event']['recur_frequency_unit'] ?? NULL;

    $unitVals = explode(CRM_Core_DAO::VALUE_SEPARATOR, $frUnits);

    // CRM 10860, display text instead of a dropdown if there's only 1 frequency unit
    if (count($unitVals) == 1) {
      $form->assign('one_frequency_unit', TRUE);
      $unit = $unitVals[0];
      $form->add('hidden', 'frequency_unit', $unit);
      if (!empty($form->_values['event']['is_recur_interval'])) {
        $unit .= "(s)";
      }
      $form->assign('frequency_unit', $unit);
    }
    else {
      $form->assign('one_frequency_unit', FALSE);
      $units = [];
      $frequencyUnits = CRM_Core_OptionGroup::values('recur_frequency_units', FALSE, FALSE, TRUE);
      foreach ($unitVals as $key => $val) {
        if (array_key_exists($val, $frequencyUnits)) {
          $units[$val] = $frequencyUnits[$val];
          if (!empty($form->_values['event']['is_recur_interval'])) {
            $units[$val] = "{$frequencyUnits[$val]}(s)";
          }
        }
      }
      $frequencyUnit = &$form->addElement('select', 'frequency_unit', NULL, $units, ['aria-label' => ts('Frequency Unit')]);
    }
    $installmentOption = ['2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'];
    $installmentOption = ['2' => '2', '3' => '3'];
    /*
    $form->add('text', 'installments', ts('installments'), $attributes['installments']);
    */
    $form->addElement('select', 'installments', NULL, $installmentOption, ['aria-label' => ts('installments')]);
    $form->addRule('installments', ts('Number of installments must be a whole number.'), 'integer');
  }


  /**
   * @param $inputParams
   * @param $form
   * @param $contactID
   */
  public static function contributionRecur(&$inputParams, &$form, $contactID) {

    // Create Recurring payment and Contribution Record with Pending Status.
    $numInstallments = $inputParams['installments'];
    $contributionFirstAmount = $contributionRecurAmount = $inputParams['amount'];
    if ($numInstallments > 0) {
      $contributionRecurAmount = floor(($inputParams['amount'] / $numInstallments) * 100) / 100;
      $contributionFirstAmount = $inputParams['amount'] - $contributionRecurAmount * ($numInstallments - 1);
    }

    // Create Params for Creating the Recurring Contribution Series and Create it
    $contributionRecurParams = [
      'contact_id' => $contactID,
      'frequency_interval' => $inputParams['frequency_interval'] ?? 1,
      'frequency_unit' => $inputParams['frequency_unit'] ?? 'month',
      'installments' => $numInstallments,
      'amount' => $contributionRecurAmount,
      'contribution_status_id' => 'In Progress',
      'currency' => $inputParams['currencyID'],
      'payment_processor_id' => $inputParams['payment_processor_id'],
      'financial_type_id' => $form->_values['event']['financial_type_id'],
    ];

    if (empty($inputParams['payment_processor_id'])) {
      $contributionRecurParams['payment_processor_id'] = 'null';
    }
    // Create Recurring record
    $resultRecur = civicrm_api3('ContributionRecur', 'create', $contributionRecurParams);

    $inputParams['contribution_recur_id'] = $resultRecur['id'];
    $inputParams['contributionRecurID'] = $resultRecur['id'];

    $contributionParams = self::getContributionParams($inputParams);
    $contributionParams['contact_id'] = $contactID;
    $contributionParams['total_amount'] = $contributionFirstAmount;
    $contributionParams['contribution_recur_id'] = $resultRecur['id'];
    $contributionParams['financial_type_id'] = $contributionRecurParams['financial_type_id'];
    $contributionParams['skipLineItem'] = 1;
    // Create Contribution records
    $result = civicrm_api3('contribution', 'create', $contributionParams);

    $inputParams['contributionID'] = $result['id'];
    $inputParams['contribution_id'] = $result['id'];
    $inputParams['total_amount'] = $contributionParams['total_amount'];
    // add participant
    // self::addParticipant($inputParams, $form, $contactID);
  }

  /**
   * @param $params
   * @return array
   */
  public static function getContributionParams($params) {
    $contributionParams = [
      'receive_date' => !empty($params['receive_date']) ? CRM_Utils_Date::processDate($params['receive_date']) : date('YmdHis'),
      'tax_amount' => $params['tax_amount'] ?? NULL,
      'amount_level' => $params['amount_level'] ?? NULL,
      'invoice_id' => $params['invoiceID'],
      'currency' => $params['currencyID'],
      'is_pay_later' => $params['is_pay_later'] ?? 0,
      'cancel_reason' => $params['cancel_reason'] ?? 0,
      'thankyou_date' => isset($params['thankyou_date']) ? CRM_Utils_Date::format($params['thankyou_date']) : NULL,
    ];

    $contributionParams['contribution_status_id'] = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Pending');

    return $contributionParams;
  }

  /**
   * Custom function to add participant record
   * @param $inputParams
   * @param $form
   * @param $contactID
   */
  public static function  addParticipant(&$inputParams, &$form, $contactID) {
    // Note this used to be shared with the backoffice form & no longer is, some code may no longer be required.
    $params = $inputParams;

    $transaction = new CRM_Core_Transaction();

    // handle register date CRM-4320
    $registerDate = NULL;
    if (!empty($params['participant_register_date']) && is_array($params['participant_register_date']) && !empty($params['participant_register_date'])) {
      $registerDate = CRM_Utils_Date::format($params['participant_register_date']);
    }
    $pendingStatuses = CRM_Event_PseudoConstant::participantStatus(NULL, "class = 'Pending'");
    $status = 'Pending from incomplete transaction';
    $value['participant_status_id'] = $value['participant_status'] = array_search($status, $pendingStatuses);

    $participantFields = CRM_Event_DAO_Participant::fields();
    $participantParams = [
      'contact_id' => $contactID,
      'event_id' => $form->getVar('_eventId') ? $form->getVar('_eventId') : $params['event_id'],
      'role_id' => CRM_Utils_Array::value('participant_role_id', $params) ?: CRM_Event_BAO_Participant::getDefaultRoleID(),
      'register_date' => ($registerDate) ? $registerDate : date('YmdHis'),
      'source' => CRM_Utils_String::ellipsify(
        isset($params['participant_source']) ? CRM_Utils_Array::value('participant_source', $params) : CRM_Utils_Array::value('description', $params),
        $participantFields['participant_source']['maxlength']
      ),
      'fee_level' => $params['amount_level'] ?? NULL,
      'is_pay_later' => CRM_Utils_Array::value('is_pay_later', $params, 0),
      'fee_amount' => $inputParams['amount'] ?? NULL,
      'discount_id' => $params['discount_id'] ?? NULL,
      'fee_currency' => $params['currencyID'] ?? NULL,
      'campaign_id' => $params['campaign_id'] ?? NULL,
    ];
    $participantParams['participant_status_id'] = $participantParams['participant_status'] = array_search($status, $pendingStatuses);

    if ($form->_action & CRM_Core_Action::PREVIEW || CRM_Utils_Array::value('mode', $params) == 'test') {
      $participantParams['is_test'] = 1;
    }
    else {
      $participantParams['is_test'] = 0;
    }

    if (!empty($params['note'])) {
      $participantParams['note'] = $params['note'];
    }
    elseif (!empty($params['participant_note'])) {
      $participantParams['note'] = $params['participant_note'];
    }

    $participantParams['discount_id'] = CRM_Core_BAO_Discount::findSet($form->getVar('_eventId'), 'civicrm_event');

    if (!$participantParams['discount_id']) {
      $participantParams['discount_id'] = "null";
    }
    $participant = CRM_Event_BAO_Participant::create($participantParams);

    $transaction->commit();


    $inputParams['participant_id'] = $participant->id;
    $inputParams['participantID'] = $participant->id;
    $form->setVar('_participantId', $participant->id);
    if (!empty($inputParams['participant_id']) && !empty($inputParams['contribution_id'])) {
      $paymentParams = [
        'participant_id' => $participant->id,
        'contribution_id' => $inputParams['contribution_id'],
      ];
      civicrm_api3('ParticipantPayment', 'create', $paymentParams);
    }
  }

  /**
   * Function to check participant payment is recurring.
   *
   * @param $participantID
   * @return bool
   */
  public static function isRecurring($participantID, $checkParentRegistration = TRUE) {
    // get the count
    $query = "SELECT count(*)
      FROM civicrm_participant cp
      INNER JOIN civicrm_participant_payment cpp ON cp.id = cpp.participant_id
      INNER JOIN civicrm_contribution cc ON (cc.id = cpp.contribution_id)
      WHERE cpp.participant_id = %1
      AND cc.contribution_recur_id IS NOT NULL
      GROUP BY cpp.participant_id
      ";
    $params = [1 => [$participantID, 'Positive']];
    $getCount = CRM_Core_DAO::singleValueQuery($query, $params);

    if ($getCount) {
      return TRUE;
    }
    // check parent participant for recurring
    if ($checkParentRegistration) {
      $parentParticiapntID = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Participant', $objectId, 'registered_by_id');
      if ($parentParticiapntID) {
        return self::isRecurring($participantID, FALSE);
      }
    }

    return FALSE;
  }


  /**
   * Function to get Amount paid till date.
   *
   * @param $participantID
   * @return int|null|string
   */
  public static function amountPaid($participantID) {
    $statuses = CRM_Contribute_BAO_Contribution::buildOptions('contribution_status_id', 'validate');
    $completedStatusID = array_search('Completed', $statuses);
    // get the count
    $query = "SELECT SUM(total_amount)
      FROM civicrm_participant cp
      INNER JOIN civicrm_participant_payment cpp ON cp.id = cpp.participant_id
      INNER JOIN civicrm_contribution cc ON (cc.id = cpp.contribution_id)
      WHERE
        cpp.participant_id = %1
        AND cc.contribution_recur_id IS NOT NULL
        AND cc.contribution_status_id = %2
      GROUP BY cpp.participant_id
      ";
    $params = [1 => [$participantID, 'Positive'], 2 => [$completedStatusID, 'Positive']];
    $getAmount = CRM_Core_DAO::singleValueQuery($query, $params);

    if (!empty($getAmount)) {
      return $getAmount;
    }

    return 0;
  }

  /**
   * @param $primaryParticipantID
   * @return int|null|string
   */
  public static function totalEventFee($primaryParticipantID) {
    $query = "select sum(fee_amount)
      from civicrm_participant
      where id = %1 OR registered_by_id = %1";
    $params = [1 => [$primaryParticipantID, 'Positive']];
    $getAmount = CRM_Core_DAO::singleValueQuery($query, $params);

    if (!empty($getAmount)) {
      return $getAmount;
    }

    return 0;
  }

  /**
   * @param $value
   * @param $eventID
   */
  public static function setSettingsConfig($value, $eventID) {
    // use settings as defined in default domain
    $domainID = CRM_Core_Config::domainID();
    $settings = Civi::settings($domainID);
    $settings->set('events_config_' . $domainID . '_' . $eventID, $value);
  }

  /**
   * @param null $eventID
   * @return array|mixed
   */
  public static function getSettingsConfig($eventID = NULL) {
    if (empty($eventID))
      return [];
    // use settings as defined in default domain
    $domainID = CRM_Core_Config::domainID();
    $settings = Civi::settings($domainID);

    return $settings->get('events_config_' . $domainID . '_' . $eventID);
  }

  /**
   * @return array
   */
  public static function getAllSettingConfig() {
    $events = CRM_Event_BAO_Event::getEvents(1, NULL, TRUE, FALSE);
    $domainID = CRM_Core_Config::domainID();
    $sql = "SELECT value FROM `civicrm_setting` WHERE `name` LIKE 'events_config_{$domainID}_%'";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $configList = [];
    while ($dao->fetch()) {
      $eventTitle = '';
      $configSetting = CRM_Utils_String::unserialize($dao->value);
      $configList[$configSetting['events_id']]['event_id'] = $configSetting['events_id'];
      if (array_key_exists($configSetting['events_id'], $events)) {
        $eventTitle = $events[$configSetting['events_id']];
      }
      $configList[$configSetting['events_id']]['event_title'] = $eventTitle;
      $configList[$configSetting['events_id']]['event_link'] =
        CRM_Utils_System::url('civicrm/admin/event/setting', "reset=1&event_id={$configSetting['events_id']}");;
      $configList[$configSetting['events_id']]['event_config'] = $configSetting;
    }

    return $configList;
  }

  /**
   * @return array
   */
  public static function getPriceSets() {
    $values = self::getPriceSetsInfo();

    $priceSets = [];
    if (!empty($values)) {
      foreach ($values as $set) {
        $priceSets[$set['item_id']] = "{$set['ps_label']} :: {$set['pf_label']} :: {$set['item_label']}";
      }
    }

    return $priceSets;
  }

  /**
   * @param null $priceSetId
   * @return array
   */
  public static function getPriceSetsOptions($priceSetId = NULL) {
    $values = self::getPriceSetsInfo($priceSetId);

    $priceSets = [];
    if (!empty($values)) {
      $currentLabel = NULL;
      $optGroup = 0;
      foreach ($values as $set) {
        // Quickform doesn't support optgroups so this uses a hack. @see js/Common.js in core
        if ($currentLabel !== $set['ps_label']) {
          //$priceSets['crm_optgroup_' . $optGroup++] = $set['ps_label'];
        }
        $priceSets[$set['item_id']] = "{$set['pf_label']} :: {$set['item_label']}";
        $currentLabel = $set['ps_label'];
      }
    }

    return $priceSets;
  }

  /**
   * @param null $priceSetId
   * @return array
   */
  public static function getPriceSetsInfo($priceSetId = NULL) {
    $params = [];
    $psTableName = 'civicrm_price_set_entity';
    if ($priceSetId) {
      $additionalWhere = 'ps.id = %1';
      $params = [1 => [$priceSetId, 'Positive']];
    }
    else {
      $additionalWhere = 'ps.is_quick_config = 0';
    }

    $sql = "
      SELECT    pfv.id as item_id,
                pfv.label as item_label,
                pf.label as pf_label,
                ps.title as ps_label
      FROM      civicrm_price_field_value as pfv
      LEFT JOIN civicrm_price_field as pf on (pf.id = pfv.price_field_id AND pf.is_active  = 1 AND pfv.is_active = 1)
      LEFT JOIN civicrm_price_set as ps on (ps.id = pf.price_set_id AND ps.is_active = 1)
      INNER JOIN {$psTableName} as pse on (ps.id = pse.price_set_id)
      WHERE  {$additionalWhere}
      ORDER BY  pf_label, pfv.price_field_id, pfv.weight
      ";

    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    $priceSets = [];
    while ($dao->fetch()) {
      $priceSets[$dao->item_id] = [
        'item_id' => $dao->item_id,
        'item_label' => $dao->item_label,
        'pf_label' => $dao->pf_label,
        'ps_label' => $dao->ps_label,
      ];
    }

    return $priceSets;
  }

  /**
   * @param $eventID
   * @param $option_id
   * @param $childNumber
   * @param bool $isJccMember
   * @return array
   */
  public static function getDiscountAmount($eventID, $option_id, $childNumber, $isJccMember = FALSE) {
    $defaultsConfig = CRM_Eventinstallment_Utils::getSettingsConfig($eventID);
    $eventFeeDetails = $defaultsConfig['events_rule'][$option_id];
    $currentDate = strtotime(date('YmdHis'));
    //$currentDate = strtotime("15 September 2021");
    //$currentDate = strtotime("15 January 2022");
    //$currentDate = strtotime("15 March 2022");
    $childNumber = ($childNumber >= 4) ? 4 : $childNumber;
    $defaultFee = $sellFee = $eventFeeDetails['regular'];
    $siblingDiscountFee = 0;
    $discountName = '';
    foreach ($eventFeeDetails as $discountDetails) {
      if ($isJccMember && is_array($discountDetails) && !empty($discountDetails['child_jcc_' . $childNumber])) {
        $discountName = $discountDetails['discount_name'];
        $startDate = self::cleanDate($discountDetails['discount_start_date']);
        $endDate = self::cleanDate($discountDetails['discount_end_date']);
        if ($startDate && $endDate && ($currentDate >= $startDate && $currentDate <= $endDate)) {
          $sellFee = $discountDetails['child_jcc_' . $childNumber];
          break;
        }
        elseif (!empty($startDate) && empty($endDate) && ($currentDate >= $startDate)) {
          $sellFee = $discountDetails['child_jcc_' . $childNumber];
          break;
        }
        elseif (empty($startDate) && !empty($endDate) && ($currentDate <= $endDate)) {
          $sellFee = $discountDetails['child_jcc_' . $childNumber];
          break;
        }
      }
      elseif (is_array($discountDetails) && !empty($discountDetails['child_' . $childNumber])) {
        $discountName = $discountDetails['discount_name'];
        $startDate = self::cleanDate($discountDetails['discount_start_date']);
        $endDate = self::cleanDate($discountDetails['discount_end_date']);
        if ($startDate && $endDate && ($currentDate >= $startDate && $currentDate <= $endDate)) {
          $sellFee = $discountDetails['child_' . $childNumber];
          break;
        }
        elseif (!empty($startDate) && empty($endDate) && ($currentDate >= $startDate)) {
          $sellFee = $discountDetails['child_' . $childNumber];
          break;
        }
        elseif (empty($startDate) && !empty($endDate) && ($currentDate <= $endDate)) {
          $sellFee = $discountDetails['child_' . $childNumber];
          break;
        }
      }
    }

    foreach ($eventFeeDetails as $discountDetails) {
      if (is_array($discountDetails) && !empty($discountDetails['sibling_' . $childNumber])) {
        $startDate = self::cleanDate($discountDetails['discount_start_date']);
        $endDate = self::cleanDate($discountDetails['discount_end_date']);
        if ($startDate && $endDate && ($currentDate >= $startDate && $currentDate <= $endDate)) {
          $siblingDiscountFee = $discountDetails['sibling_' . $childNumber];
          break;
        }
        elseif (!empty($startDate) && empty($endDate) && ($currentDate >= $startDate)) {
          $siblingDiscountFee = $discountDetails['sibling_' . $childNumber];
          break;
        }
        elseif (empty($startDate) && !empty($endDate) && ($currentDate <= $endDate)) {
          $siblingDiscountFee = $discountDetails['sibling_' . $childNumber];
          break;
        }
      }
    }

    $sellFee = $sellFee - $siblingDiscountFee;
    $discountAmount = $defaultFee - $sellFee - $siblingDiscountFee;

    return [$defaultFee, $sellFee, $discountAmount];
  }

  /**
   * @param $date
   * @return false|int|void
   */
  public static function cleanDate($date) {
    if (empty($date))
      return;
    $mysqlDate = CRM_Utils_Date::isoToMysql($date);

    return $mysqlDate = strtotime($mysqlDate);
  }

  /**
   *  Helper function that fetches a specified list of fields
   *  for a given contact or list of contacts.
   *
   * @param $fields
   * @param null $contactIds
   * @return bool|array
   */
  public static function getContactData($fields, $contactIds = NULL) {
    $params = [
      'is_deceased' => FALSE,
      'is_deleted' => FALSE,
    ];

    $params['id'] = is_array($contactIds) ? ['IN' => $contactIds] : ['IN' => [$contactIds]];

    //todo: Some massage of fields to fetch all requested data
    $fieldsToFetch = $fields;
    $fieldMapping = [];
    $relatedObjects = [];
    foreach ($fieldsToFetch as &$fieldName) {
      @ list($field, $location, $type) = explode("-", $fieldName);

      if ($location == "Primary") {
        if (strpos($field, "custom") !== FALSE) {
          $objectType = substr($field, 0, strpos($field, "_"));

          if (!array_key_exists($objectType, $relatedObjects)) {
            $relatedObjects[$objectType] = [];
          }

          if (!array_key_exists($location, $relatedObjects[$objectType])) {
            $relatedObjects[$objectType][$location] = [];
          }

          $relatedObjects[$objectType][$location][$field] = $fieldName;

        }
        else {
          $fieldMapping[$field] = $fieldName;
          $fieldName = $field;
        }

      }
      elseif (is_numeric($location)) {

        if (strpos($field, "custom") !== FALSE) {
          $objectType = substr($field, 0, strpos($field, "_"));
        }
        else {
          if ($field == "phone" || $field == "email") {
            $objectType = $field;
          }
          else {
            $objectType = "address";
          }
        }


        if (!array_key_exists($objectType, $relatedObjects)) {
          $relatedObjects[$objectType] = [];
        }

        if (!array_key_exists($location, $relatedObjects[$objectType])) {
          $relatedObjects[$objectType][$location] = [];
        }

        $relatedObjects[$objectType][$location][$field] = $fieldName;

      }
    }

    //Add some API Chaining for related objects that wont be auto-fetched via Contact.get
    foreach ($relatedObjects as $entityType => $locationTypeData) {
      $locationTypes = array_keys($locationTypeData);
      $chainKey = "api." . ucfirst($entityType) . ".get";
      if (in_array("Primary", $locationTypes)) {
        $params[$chainKey] = [];
      }
      else {
        $params[$chainKey] = ["location_type_id" => ["IN" => $locationTypes]];
      }
    }
    // SUP-1707 exclude participant fields
    $api = civicrm_api3('CustomGroup', 'get', [
      'extends' => 'Participant',
      'is_active' => 1,
      'return' => 'id',
    ]);
    $group_ids = array_keys($api['values']);

    $field_ids = [];
    foreach ($fieldsToFetch as $ftf) {
      if (strpos($ftf, 'custom_') === 0) {
        $field_ids[] = substr($ftf, 7);
      }
    }
    try {
      $api = civicrm_api3('CustomField', 'get', [
        'id' => [
          'IN' => $field_ids,
        ],
        'custom_group_id' => [
          'IN' => $group_ids,
        ],
        'return' => 'id',
      ]);
    }
    catch (Exception $e) {
      // probably no custom fields, which is fine.
      trigger_error($e->getMessage());
    }
    $field_ids = [];
    foreach (array_keys($api['values']) as $id) {
      $field_ids[] = "custom_$id";
    }
    // SUP-1707
    //exclude Participant Fields from data to pre-populate the form
    $params['return'] = array_diff($fieldsToFetch, $field_ids);

    //Fetch the data
    $result = civicrm_api3('Contact', 'get', $params);

    if ($result['is_error'] == 0 && $result['count'] > 0) {
      $contacts = [];
      foreach ($result['values'] as $cid => $data) {
        foreach ($fieldMapping as $name => $oldName) {
          if (array_key_exists($name, $data) && !array_key_exists($oldName, $data)) {
            $data[$oldName] = $data[$name];
          }
        }

        //Mix in the related objects data.
        foreach ($relatedObjects as $entity => $locationTypeData) {
          $chainKey = "api." . ucfirst($entity) . ".get";
          foreach ($data[$chainKey]['values'] as $entityData) {
            if (array_key_exists($entityData['location_type_id'], $locationTypeData)) {
              foreach ($locationTypeData[$entityData['location_type_id']] as $name => $oldName) {
                if (!array_key_exists($oldName, $data)) {
                  if (array_key_exists($name, $entityData)) {
                    $data[$oldName] = $entityData[$name];
                  }
                  elseif (array_key_exists($name . "_id", $entityData)) {
                    $data[$oldName] = $entityData[$name . "_id"];
                  }
                }
              }
            }

            if ($entityData['is_primary'] == 1 && array_key_exists("Primary", $locationTypeData)) {
              foreach ($locationTypeData["Primary"] as $name => $oldName) {
                if (!array_key_exists($oldName, $data)) {
                  if (array_key_exists($name, $entityData)) {
                    $data[$oldName] = $entityData[$name];
                  }
                  elseif (array_key_exists($name . "_id", $entityData)) {
                    $data[$oldName] = $entityData[$name . "_id"];
                  }
                }
              }
            }
          }
        }

        //Only return the fields that were asked for.
        $contacts[$cid] = array_intersect_key($data, array_flip($fields));
      }

      //Decide what to return and in what format
      if (!is_array($contactIds)) {
        return $contacts[$contactIds];
      }

      return $contacts;
    }

    return FALSE;
  }

  /**
   * @param $form
   * @return array
   */
  public static function contactSequenceForRegistration($form) {
    $params = $form->getVar('_params');
    $currentContactID = $form->getLoggedInUserContactID();
    $childContacts = $childSortContacts = $parentContacts = [];
    foreach ($params[0] as $k => $v) {
      if (strpos($k, 'contacts_child_') === 0) {
        [, , $cid] = explode('_', $k);
        $childContacts[$cid] = $cid;
      }
      elseif (strpos($k, 'contacts_parent_') === 0) {
        [, , $cid] = explode('_', $k);
        $parentContacts[$cid] = $cid;
      }
    }

    unset($parentContacts[$currentContactID]);
    sort($childContacts);
    $i = 1;
    foreach ($childContacts as $cid) {
      $childSortContacts[$i] = $cid;
      $i++;
    }
    $finalContactList = [];

    $i = 1;
    foreach ($parentContacts + $childContacts as $cid) {
      $finalContactList[$i] = $cid;
      $i++;
    }

    return [$finalContactList, $childSortContacts, $parentContacts];
  }

  /**
   * @return array
   */
  public static function getCiviCRMFields() {
    $civicrmFields = CRM_Contact_Form_Search_Builder::fields();
    $cleanFields = [];
    foreach ($civicrmFields as $fieldName => $fieldDetail) {
      $cleanFields[$fieldName] = $fieldDetail['title'];
    }

    return $cleanFields;
  }

  public static function _add_reload_textfield(&$form) {
    $buttonName = $form->getButtonName('reload');
    if (!$form->elementExists($buttonName)) {
      $form->addElement('submit', $buttonName, ts('Check for discount'), ['formnovalidate' => 1, 'style' => 'display:none;']);
      $template = CRM_Core_Smarty::singleton();
      $bhfe = $template->get_template_vars('beginHookFormElements');
      if (!$bhfe) {
        $bhfe = [];
      }
      $bhfe[] = 'discountcheck';
      $bhfe[] = $buttonName;
      $form->assign('beginHookFormElements', $bhfe);
    }
  }

  /**
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  public static function membershipTypeCurrentDomain() {
    $result = civicrm_api3('MembershipType', 'get', [
      'sequential' => 1,
      'return' => ["id", "name"],
    ]);
    $membershipType = [];
    foreach ($result['values'] as $details) {
      $membershipType[$details['id']] = $details['name'];
    }

    return $membershipType;
  }
}
