{if $recurringPaymentProcessor}
{literal}
<script type="text/javascript">
    var paymentProcessorMapper = [];
    {/literal}
    {foreach from=$recurringPaymentProcessor item="paymentProcessor" key="index"}{literal}
    paymentProcessorMapper[{/literal}{$index}{literal}] = '{/literal}{$paymentProcessor}{literal}';
    {/literal}
    {/foreach}
    {literal}
    CRM.$(document).ready(function() {
    // show block in right order
    CRM.$('#recurringFields').insertAfter('#priceSet');
        // show/hide recurring block
        CRM.$('.crm-event-manage-fee-form-block-payment_processor input[type="checkbox"]').change(function(){
            showRecurring( checked_payment_processors() );
        });
        showRecurring( checked_payment_processors() );
    });
    function checked_payment_processors() {
        var ids = [];
        CRM.$('.crm-event-manage-fee-form-block-payment_processor input[type="checkbox"]').each(function () {
            if (CRM.$(this).prop('checked')) {
                var id = CRM.$(this).attr('id').split('_')[2];
                ids.push(id);
            }
        });
        return ids;
    }

    function showRecurring( paymentProcessorIds ) {
        var display = true;
        CRM.$.each(paymentProcessorIds, function (k, id) {
            if (CRM.$.inArray(id, paymentProcessorMapper) == -1) {
                display = false;
            }
        });

        if (display) {
            CRM.$('#recurringContribution').show();
        } else {
            if (CRM.$('#is_recur').prop('checked')) {
                CRM.$('#is_recur').prop('checked', false);
                CRM.$('#recurFields').hide();
            }
            CRM.$('#recurringContribution').hide();
        }
    }
</script>
{/literal}
    <div id="recurringFields">
        <table class="form-layout-compressed">
            <tr id="recurringContribution" class="crm-event-form-block-is_recur"><td scope="row" class="label" width="20%">{$form.is_recur.label}</td>
                <td>{$form.is_recur.html}<br />
                    <span class="description">{ts}Check this box if you want to give users the option to make recurring event payment. This feature requires that you use a payment processor which supports recurring billing / subscriptions functionality.{/ts} {docURL page="user/contributions/payment-processors"}</span>
                </td>
            </tr>
            <tr id="recurFields" class="crm-event-form-block-recurFields"><td>&nbsp;</td>
                <td>
                    <table class="form-layout-compressed">
                        <tr class="crm-event-form-block-recur_frequency_unit"><td scope="row" class="label">{$form.recur_frequency_unit.label}<span class="crm-marker" title="This field is required.">*</span></td>
                            <td>{$form.recur_frequency_unit.html}<br />
                                <span class="description">{ts}Select recurring units supported for recurring payments.{/ts}</span></td>
                        </tr>
                        <tr class="crm-event-form-block-is_recur_interval"><td scope="row" class="label">{$form.is_recur_interval.label}</td>
                            <td>{$form.is_recur_interval.html}<br />
                                <span class="description">{ts}Can users also set an interval (e.g. every '3' months)?{/ts}</span></td>
                        </tr>
                        <tr class="crm-event-form-block-is_recur_installments"><td scope="row" class="label">{$form.is_recur_installments.label}</td>
                            <td>{$form.is_recur_installments.html}<br />
                                <span class="description">{ts}Restrict total number of installments.{/ts}</span></td>
                        </tr>
                    </table>
                </td>
            </tr>

        </table>
    </div>
    {if $form.is_recur}
        {include file="CRM/common/showHideByFieldValue.tpl"
        trigger_field_id    ="is_recur"
        trigger_value       ="true"
        target_element_id   ="recurFields"
        target_element_type ="table-row"
        field_type          ="radio"
        invert              = "false"
        }
    {/if}
{/if}
