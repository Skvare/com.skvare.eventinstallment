{if $form.is_recur}
{literal}
    <script type="text/javascript">
        CRM.$(document).ready(function() {
            // show block in right order
            var moneyFormat    = '{/literal}{$moneyFormat}{literal}';
            CRM.$('#event_recurring_block').insertAfter('.price_set-section');
            //CRM.$('#_qf_Register_upload-bottom').html('Continue');
            CRM.$('.payment_information, .payment_processor-section').show();
            CRM.$('.payment_options-group, .payment_processor-section').show();
            CRM.$('#billing-payment-block, #payment_information').show();
            CRM.$('#pricevalue, #installments, #is_recur').change(function() {
                var total_amount_tmp =  CRM.$('#pricevalue').data('raw-total');
                if (total_amount_tmp && CRM.$('#installments').val() && CRM.$('#is_recur:checked').length) {
                    //var installments = CRM.$('#installments').val();
                    var installments = CRM.$('#installments :selected').val()
                    var newAmount = total_amount_tmp / installments;
                    var newAmountFormatted = CRM.formatMoney(newAmount, false, moneyFormat);
                    CRM.$('#amountperinstallment').html(newAmountFormatted);
                }
            });
        });
    </script>
{/literal}
    <div id="event_recurring_block" class="crm-public-form-item crm-section{$form.is_recur.name}-section">
        <div class="label">&nbsp;</div>
        <div class="content">
            {$form.is_recur.html}
            {$form.is_recur.label} <span id="amountperinstallment1"></span>fee {ts}every{/ts}
            {if $is_recur_interval}
                {$form.frequency_interval.html}
            {/if}
            {if $one_frequency_unit}
                {$frequency_unit}
            {else}
                {$form.frequency_unit.html}
            {/if}
            {if $is_recur_installments}
                <span id="recur_installments_num">
          {ts}for{/ts} {$form.installments.html} {$form.installments.label}
          </span>
            {/if}
            <div id="paymentSummary" class="description"></div>
            <div id="recurHelp" class="description">
                {$recurringHelpText}
            </div>
        </div>
        <div class="clear"></div>
    </div>
{/if}
{if $relatedContacts}
{literal}
<script type="text/javascript">
    CRM.$(document).ready(function() {
        // show block in right order
        //CRM.$('#_qf_Register_upload-bottom').html('Continue');
        CRM.$('.payment_information, .payment_processor-section').show();
        CRM.$('.payment_options-group, .payment_processor-section').show();
        CRM.$('#billing-payment-block, #payment_information').show();
    });
</script>
{/literal}
{/if}
