<div class="crm-block crm-form-block crm-event-group-form-block">
    <div class="action-link-invoice">
        {crmButton p='civicrm/admin/event/setting'
        q="reset=1"
        icon="plus-circle"}{ts}Add Discount for Event{/ts}{/crmButton}
    </div>
    <table class="selector">
        <tr class="columnheader">
            <th>{ts}Event ID{/ts}</th>
            <th>{ts}Event Name{/ts}</th>
            <th>{ts}{/ts}</th>
        </tr>
        {counter start=0 skip=1 print=false}
        {foreach from=$configList key=event_id item=eventConfig}
            <tr id='rowid{$event_id}' class="{cycle values="odd-row,even-row"}">
                <td><a href="{$eventConfig.event_link}">{$eventConfig.event_id}</a></td>
                <td>{$eventConfig.event_title}</td>
                <td></td>
            </tr>
        {/foreach}
        {if $total_amount}
            <tr class="{cycle values="odd-row,even-row"}">
                <td colspan="3">Total</td><td>{$total_amount|crmMoney}</td>
            </tr>
        {/if}
    </table>
</div>