/**
 * Initializes the readonly state for specified fields and adds an overlay message.
 *
 * This script targets elements with IDs "disallowed_keys" and "moderation_keys", sets them as readonly,
 * and if they are not already wrapped in a container with the "dbu-overlay-container" class, it wraps them and
 * appends an overlay div with an explanatory message.
 *
 * @requires jQuery
 */
jQuery(document).ready(function ($) {
  /**
   * jQuery selector for target elements to be set as readonly.
   * @type {jQuery}
   */
  $("#disallowed_keys, #moderation_keys")
    .attr("readonly", "readonly")
    .each(function () {
      /**
       * The current element wrapped as a jQuery object.
       * @type {jQuery}
       */
      var $this = $(this);

      // Check if the parent element does not already have the overlay container class.
      if (!$this.parent().hasClass("dbu-overlay-container")) {
        // Wrap the element in a container.
        $this.wrap('<div class="dbu-overlay-container"></div>');
        // Append an overlay message after the element.
        $this.after(
          '<div class="dbu-overlay">This field is managed by Dynamic Blacklist Updater plugin</div>'
        );
      }
    });
});
