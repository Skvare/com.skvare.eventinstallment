{if $form.pricefield_for_discount}
{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      $('.crm-price-field-form-block_pricefield_for_discount').insertAfter('.crm-price-field-form-block-is_display_amounts');
    });
  </script>
{/literal}

  <table class="form-layout-compressed" style="display: none">
    <tr class="crm-price-field-form-block_pricefield_for_discount">
      <td class="label">{$form.pricefield_for_discount.label}</td>
      <td>{$form.pricefield_for_discount.html}</td>
    </tr>
  </table>
{/if}
