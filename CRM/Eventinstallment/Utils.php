<?php
use CRM_Eventinstallment_ExtensionUtil as E;

class CRM_Eventinstallment_Utils {

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

    $form->add('checkbox', 'is_recur', ts('I want pay'),NULL);

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
    $installmentOption = ['2' => '2','3' => '3', '4' => '4', '5' => '5', '6' => '6'];
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
      'frequency_interval' => $inputParams['frequency_interval'] ?? NULL,
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
    self::addParticipant($inputParams, $form, $contactID);
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
  public static function addParticipant(&$inputParams, &$form, $contactID) {
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
}