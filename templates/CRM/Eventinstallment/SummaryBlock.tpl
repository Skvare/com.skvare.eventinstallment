<div class="crm-section no-label partial_payment" id="partial_payment">
    <div class="content">
            {if $frequency_interval > 1}
                <p>
                    {ts 1=$frequency_interval 2=$frequency_unit 3=$installments, 4=$installmentAmount}I want to pay %4 every %1 %2s for %3 installments.{/ts}
                </p>
            {else}
                <p>
                    {ts 1=$frequency_unit 2=$installments 3=$installmentAmount}I want to pay %3 amount every %1 for %2 installments.{/ts}
                </p>
            {/if}
            <p>{ts}Your first installment will be processed once you complete the confirmation step.{/ts}</p>
    </div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
    cj('#partial_payment').insertAfter('.total_amount-section');
</script>
