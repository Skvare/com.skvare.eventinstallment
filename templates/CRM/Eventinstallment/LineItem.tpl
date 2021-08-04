{literal}
<script type="text/javascript">
    cj('.participant_info-group .header-dark').each(function(i, v) {
        cj(this).html('Child Participant');
    });
    cj('.participant_info-group .header-dark').first().html('Participant : Not Attending')
    cj('.participant_info-group').first().hide();
    cj('.event_fees-group strong').each(function(i, v) {
        cj(this).html('Participant');
    });
    //cj('.event_fees-group strong').first().html('Discount Section')
</script>
{/literal}