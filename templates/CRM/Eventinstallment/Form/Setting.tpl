<div id="help">
  <p>Event Discount application Configuration form.</p>
</div>

<div class="crm-block crm-form-block">

  <table class="form-layout">
    <tr>
      <td class="label">{$form.events_relationships.label}</td>
      <td>
          {$form.events_relationships.html}
        <div class="description">Select Relationship with appropriate direction to pull the list of contact in relationship with logged in user. This Contacts will be available for main listing page.</div>
      </td>
    </tr>
      {*
    <tr>
      <td class="label">{$form.events_jcc_discount.label}</td>
      <td>
          {$form.events_jcc_discount.html} - {$form.events_jcc_discount_type.html}<br/>
        <div class="description">This discount would get applied on total amount get from regular discount like early bird and sibling disount. This will be only available if parent is Member of Jewish Community Center</div>
      </td>
    </tr>
      *}
    <tr>
      <td class="label">{$form.events_specal_discount_group.label}</td>
      <td>
          {$form.events_specal_discount_group.html} - {$form.events_specal_discount.html} - {$form.events_specal_discount_type.html}<br/>
        <div class="description">This discount would be available to the contact present in this group.</div>
      </td>
    </tr>
    <tr>
      <td class="label">{$form.events_financial_discount_group.label}</td>
      <td>
          {$form.events_financial_discount_group.html}
        <div class="description">This discount would be available to the contact present in this group.</div>
      </td>
    </tr>
    <tr>
      <td class="label">{$form.events_id.label}</td>
      <td>
          {$form.events_id.html}
        <div class="description"></div>
      </td>
    </tr>
    <tr>
      <td class="label">{$form.events_jcc_field.label}</td>
      <td>
          {$form.events_jcc_field.html}
        <div class="description">To use JCC Disount feature, choose JCC field from CiviCRM, it will check JCC value on parent contact and give discount to child.</div>
      </td>
    </tr>
      {foreach from=$price_fields key=type_id item=label}
          {if $type_id}
            <tr><td colspan="2">
                <fieldset>
                  <legend>{$label}</legend>
                    {if $index GT 1}
                      <div><i class="crm-i fa-clone action-icon" fname="{$previous_type_id}" tname="{$type_id}"><span class="sr-only">$text</span></i> Copy rows from {$previous_label}</div>{/if}

                    {assign var=previous_type_id value=$type_id}
                    {assign var=previous_label value=$label}
                  <table>
                    <tr class="columnheader">
                      <td>{ts}Discount Set{/ts}</td>
                      <td>{ts}Start Date{/ts}</td>
                      <td>{ts}End Date{/ts}</td>
                      <td>{ts}Child 1{/ts}</td>
                      <td>{ts}Child 2{/ts}</td>
                      <td>{ts}Child 3{/ts}</td>
                      <td>{ts}Child 4 and more{/ts}</td>
                    </tr>

                      {assign var=evenodd value="odd-row,odd-row,odd-row,even-row,even-row,even-row"}
                      {section name=rowLoop start=1 loop=6}
                          {assign var=index value=$smarty.section.rowLoop.index}
                        <tr class="form-item {cycle values=$evenodd}" style="border-top:1pt solid black;">
                          <td>{$form.events_rule.$type_id.$index.discount_name.html}</td>
                          <td>{$form.events_rule.$type_id.$index.discount_start_date.html} </td>
                          <td>{$form.events_rule.$type_id.$index.discount_end_date.html} </td>

                          <td>{$form.events_rule.$type_id.$index.child_1.html} </td>
                          <td>{$form.events_rule.$type_id.$index.child_2.html} </td>
                          <td>{$form.events_rule.$type_id.$index.child_3.html} </td>
                          <td>{$form.events_rule.$type_id.$index.child_4.html} </td>
                        </tr>

                        <tr class="form-item {cycle values=$evenodd}" style="border-top:1pt dotted black;">
                          <td colspan="3">JCC Member Fee<br>
                            <span class="description">keep empty box empty in case you do not want to provide discount for certain child.</span>
                              <br>
                              <span class="description">If amount present then it will JCC amount instead of above row amount.</span>
                          </td>
                          <td>{$form.events_rule.$type_id.$index.child_jcc_1.html} </td>
                          <td>{$form.events_rule.$type_id.$index.child_jcc_2.html} </td>
                          <td>{$form.events_rule.$type_id.$index.child_jcc_3.html} </td>
                          <td>{$form.events_rule.$type_id.$index.child_jcc_4.html} </td>
                        </tr>
                        <tr class="form-item {cycle values=$evenodd}" style="border-bottom:1pt solid black;border-top:1pt dotted black;">
                          <td colspan="3">Sibling Discount<br>
                            <span class="description">keep empty discount text block for not giving any discount.</span></td>
                          <td>{$form.events_rule.$type_id.$index.sibling_1.html} </td>
                          <td>{$form.events_rule.$type_id.$index.sibling_2.html} </td>
                          <td>{$form.events_rule.$type_id.$index.sibling_3.html} </td>
                          <td>{$form.events_rule.$type_id.$index.sibling_4.html} </td>
                        </tr>
                      {/section}
                  </table>
                  <table class="form-layout">
                    <tr>
                      <td class="label">{$form.events_rule.$type_id.regular.label}</td>
                      <td>
                          {$form.events_rule.$type_id.regular.html}<br/>
                        <span class="description">Regular Price Fee.</span>
                      </td>
                    </tr>

                  </table>
                </fieldset>
              </td></tr>
          {/if}
      {/foreach}
  </table>

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>

</div>

{literal}
  <script type="text/javascript">
      CRM.$(function($) {
          function copyFieldValues( fname , tname) {
              $('[id^="events_rule_'+ fname +'"]').each(function(i, v) {
                  var source_id = $(this).attr('id');
                  var isDateElement     = $(this).attr('format');
                  var source_array = source_id.split('_');
                  source_array.splice(2, 1, tname);
                  var target_id = source_array.join('_');
                  $('#'+target_id).val($('#'+source_id).val()).trigger('change');
                  //console.log('ID :' + source_id + ' > ' + target_id);
              });
          };
          //bind the click event for action icon
          $('.action-icon').click(function( ) {
              copyFieldValues($(this).attr('fname'), $(this).attr('tname'));
          });
      });

  </script>
{/literal}