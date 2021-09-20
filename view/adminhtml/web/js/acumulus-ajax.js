"use strict";

/**
 * Handles the ajax request for an Acumulus form/widget.
 *
 * - The Acumulus form/widget gets loaded dynamically by Magento on activating
 *   the tab in which it resides.
 * - When an element in an Acumulus form or widget that can initiate an ajax
 *   request to the server gets clicked, this function should be executed.
 * - The clicked element should be part of an Acumulus form or widget, contained
 *   in a wrapping element having a class acumulus-area.
 * - That wrapping element should have 2 data attributes:
 *   - 'data-acumulus-wait': the translation of "Please wait" to set as text on
 *     the clicked button.
 *   - 'data-acumulus-url': the url to send the request to, parameter
 *     isAjax=true to be added.
 * - The name of the element that got clicked, plus all values of non-button
 *   form elements in the Acumulus area are sent tot the given url.
 * - The response should contain a responseText containing the html to replace
 *   the area with.
 * - This js does not depend on jQuery, however, it does depend on Prototype js
 *   (the Ajax object).
 */
function acumulusAjaxSubmit(event) {
  const clickedElt = this;
  clickedElt.disabled = true;

  // Area is the element that is going to be replaced and serves as the
  // area in which we will search for form elements.
  const area = clickedElt.closest(".acumulus-area");
  if (area) {
    clickedElt.value = area.getAttribute('data-acumulus-wait');

    // The data we are going to send consists of:
    // - clicked: the name of the element that was clicked, the name should make
    //   clear what action is requested on the server and, optionally, on what
    //   object.
    // - {values}: values of all form elements in area: input, select and
    //   textarea, except buttons (inputs with type="button").
    const data = {
      clicked: clickedElt.name,
    };

    // Area is not necessarily a form node, in which case FormData will not
    // work. So we clone area into a temporary form node.
    const form = document.createElement('form');
    form.appendChild(area.cloneNode(true));
    const formData = new FormData(form);
    for (let entry of formData.entries()) {
      data[entry[0]] = entry[1];
    }

    let url = area.getAttribute('data-acumulus-url');
    url += url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true';
    new Ajax.Request(url, { // Prototype
      parameters: data,
      loaderArea: area,
      onSuccess: function (transport) {
        if (transport.responseText) {
          area.insertAdjacentHTML('beforebegin', transport.responseText);
          const newArea = area.previousElementSibling;
          area.parentNode.removeChild(area);
          acumulusAjaxHandling(newArea);
        } else {
          clickedElt.parentNode.insertAdjacentHTML(
            'beforebegin',
            "<div class=\"admin__field notice notice-error\"><label>✖</label><div class=\"control-value admin__field-value\">Error during sending or processing request.</div></div>"
          );
        }
      }
    });
  }
  else {
    clickedElt.value = "Error: no area";
  }
}

/**
 * Binds the ajax submit handler to the click events on the designated elements.
 */
function acumulusAjaxHandling(elt) {
  const elements = elt.getElementsByClassName("acumulus-ajax");
  for (let i = 0; i < elements.length; i++) {
    elements[i].onclick = acumulusAjaxSubmit;
  }
}

/**
 * Triggers a click() on load for designated elements.
 */
function acumulusAutoClick() {
  const elements = document.getElementsByClassName("acumulus-auto-click");
  for (let i = 0; i < elements.length; i++) {
    elements[i].click();
  }
}
