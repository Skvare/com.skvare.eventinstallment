# com.skvare.eventinstallment

![Screenshot](images/screenshot.png)

Event Installment changes online event registration so that a logged-in parent registers related contacts (usually their children) for an event, with custom pricing and optional installment payments.

- **Related-contact signup.** The Register page lists the logged-in user and the contacts related to them through the configured relationship types. A contact can be required to have an active membership of selected types, and the user can be required to be in a "paid" group. Each checked child becomes an additional participant, and their contact details are filled in automatically. The parent can also register themselves if the event's `Multireg.Parents_Can_Register` custom field allows it. Otherwise the parent is recorded as a "Not Attending" participant and only the children are charged.
- **Child pricing.** For each discount-enabled price option you set a regular fee and up to six dated discount windows (early bird and similar). Each window has fees for child 1, 2, 3 and 4+, alternative fees for JCC members (decided by a contact field on the parent), and a sibling discount per child number.
- **Group discounts.** Two contribution-level discounts can be added on the Confirm page as line items:
  - a *special discount* (fixed or percent) for members of a group;
  - a *financial assistance discount* for members of a group, where the amount and type come from fields on the parent's contact record.
- **Installments.** Paid events can let the registrant split the total into 2–12 recurring installments with a recurring-capable payment processor. The first installment is charged on Confirm.
- **Administration.** Each event gets a "Signup and Discount" tab under Manage Event. A listing of configured events is at **Administer › CiviEvent › Custom Event Signup Setting**.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

- CiviCRM 6.16 or later; PHP 8.1 or later (tested on PHP 8.3, Smarty 5).
- **Installment payments need a CiviCRM core patch.** The patch:
  - dispatches `hook_civicrm_eventinstallment_pre` from `CRM_Event_Form_Registration_Confirm` before the payment is processed;
  - lets the main participant skip price selection when the session flag `event_skip_main_parent` is set.
- Discounts and signup work without the patch.

## Installation

Install with `cv` or through the Extensions page:

```bash
cv dl com.skvare.eventinstallment@https://github.com/Skvare/com.skvare.eventinstallment/archive/master.zip
cv en eventinstallment
```

Installing adds four columns to `civicrm_event`: `is_recur`, `recur_frequency_unit`, `is_recur_interval` and `is_recur_installments`. Uninstalling leaves them in place, so the settings survive a reinstall.

## Configuration

1. **Fees tab** (Manage Event › Fees). When a live payment processor that supports recurring payments is enabled, you get:
   - an *Enable Installment* option;
   - the allowed frequency units;
   - whether registrants may choose the interval;
   - the maximum number of installments (2–12).
2. **Price set.** Edit each price field the custom pricing should apply to (Select or Radio) and check **Is this Discount Field**. Price sets that are just a list of fee levels (quick config) don't need this. For the group discounts, add options named `Special_Discount` and `Financial_Assistance_Discount` to a discount price field. They are hidden from registrants and used for the discount line items.
3. **Signup and Discount tab** (Manage Event). Set:
   - the relationship types used to find related contacts;
   - the membership types and "paid" groups;
   - the special and financial assistance discount groups and fields;
   - the JCC field;
   - the fees and discount windows for each discount price option.

   Directional relationship types are always matched with the logged-in user on the B side (for example "Parent of"), whichever direction is selected.

Each event's settings are stored in the `events_config_<domain id>_<event id>` setting of the current domain. The discount-field flag is stored in `pricefield_for_discount_fid_<price field id>`.

Anonymous visitors to a configured event see a login prompt (`templates/AccessDenied.tpl`) instead of the registration form.

## Known issues

- Only five of the six discount windows are shown on the Signup and Discount tab.
- The per-installment amount shown on the Register page is a plain division. The amount charged is rounded up to the cent.
- `civicrm/admin/event/setting` is an older per-event settings form that duplicates the Signup and Discount tab.
