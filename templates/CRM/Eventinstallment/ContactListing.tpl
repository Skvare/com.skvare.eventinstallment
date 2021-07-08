<div class="crm-block crm-form-block crm-event-group-form-block">
    <table class="selector">
        {assign var="currentUser" value=''}
        {counter start=0 skip=1 print=false}
        {foreach from=$relatedContacts key=contactID item=contact}
            {if $contact.is_parent}
            {assign var="elementName" value=contacts_parent_$contactID}
                {if $contactID eq $currentContactID}
                    {assign var="currentUser" value='currentuser'}
                {/if}
            <tr id='rowid{$contactID}' class="{cycle values="odd-row,even-row"}">
                <td>{$form.$elementName.html} - {$contact.display_name}</td>
                <td>{$contact.explanation}</td>
            </tr>
            {/if}
        {/foreach}
        {foreach from=$relatedContacts key=contactID item=contact}
            {if ! $contact.is_parent}
                {assign var="elementName" value=contacts_child_$contactID}
            <tr id='rowid{$contactID}' class="{cycle values="odd-row,even-row"}">
                <td>{$form.$elementName.html} - {$contact.display_name}</td>
                <td>{$contact.explanation}</td>
            </tr>
            {/if}
        {/foreach}
    </table>
</div>
{literal}
<script type="text/javascript">
    currentContactID = '{/literal}{$currentContactID}{literal}';
    CRM.$(function($) {
        $('.crm-event-group-form-block').insertAfter('.additional_participants-section');
    });

    function calculateAdditionalParticipant() {
        var additionalParticiapnt = 0;
        CRM.$('[id^="contacts_"]').each(function(k, v) {
            if(this.checked) {
                var source_id = CRM.$(this).attr('id');
                var source_array = source_id.split('_');
                var p_contact_id = source_array.pop();
                if (p_contact_id != currentContactID) {
                    additionalParticiapnt++;
                }
            }
        });
        console.log('additionalParticiapnt : ' + additionalParticiapnt);
        if (additionalParticiapnt == '0') {
            additionalParticiapnt = '';
        }
        console.log('additionalParticiapnt 2: ' + additionalParticiapnt);
        CRM.$("#additional_participants").val(additionalParticiapnt);
    }

    CRM.$('[id^="contacts_"]').change(function(){
        calculateAdditionalParticipant();
    });
    CRM.$('.currentUser').change(function() {
        CRM.$("#_qf_Register_reload" ).trigger( "click" );
    });
    calculateAdditionalParticipant();
</script>
{/literal}