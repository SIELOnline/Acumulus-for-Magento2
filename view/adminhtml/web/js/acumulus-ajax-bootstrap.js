/*
 * Delayed loading of the actual js.
 */
require([
    "prototype",
    "Siel_AcumulusMa2/js/acumulus-ajax",
    // These 2 are needed to get a datepicker as Magento does not yet use html5
    // date fields.
    "jquery",
    "mage/calendar"
  ],
  function() {
    acumulusAjaxHandling();
    acumulusAutoClick();
  }
);
