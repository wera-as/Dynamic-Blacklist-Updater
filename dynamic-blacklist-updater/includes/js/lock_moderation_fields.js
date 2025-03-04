jQuery(document).ready(function ($) {
  $("#disallowed_keys, #moderation_keys")
    .attr("readonly", "readonly")
    .each(function () {
      var $this = $(this);
      if (!$this.parent().hasClass("dbu-overlay-container")) {
        $this.wrap('<div class="dbu-overlay-container"></div>');
        $this.after(
          '<div class="dbu-overlay">This field is managed by Dynamic Blacklist Updater plugin</div>'
        );
      }
    });
});
