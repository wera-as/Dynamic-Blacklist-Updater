/**
 * Sets up an event listener on the "show-blacklist" checkbox to toggle the display
 * of the blacklist textarea when the DOM content is fully loaded.
 */
document.addEventListener("DOMContentLoaded", function () {
  /**
   * The checkbox element that toggles the visibility of the blacklist textarea.
   * @type {HTMLInputElement}
   */
  const checkbox = document.getElementById("show-blacklist");

  /**
   * The textarea element whose display is toggled based on the checkbox state.
   * @type {HTMLElement}
   */
  const textarea = document.getElementById("blacklist-textarea");

  /**
   * Handles the change event on the checkbox to show or hide the textarea.
   *
   * @param {Event} event - The event object associated with the change event.
   */
  checkbox.addEventListener("change", function (event) {
    textarea.style.display = this.checked ? "block" : "none";
  });
});
