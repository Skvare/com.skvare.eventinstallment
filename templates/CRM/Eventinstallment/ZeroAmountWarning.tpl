<div id="eventinstallment-zero-amount" class="messages warning no-popup">
  <i class="crm-i fa-exclamation-triangle" role="img" aria-hidden="true"></i>
  {$zeroAmountMessage}
</div>
{literal}
<script type="text/javascript">
    // Replaces core's "Click Register to complete your registration".
    cj('#eventinstallment-zero-amount').insertBefore('.continue_message-section');
    cj('.continue_message-section').hide();
</script>
{/literal}
