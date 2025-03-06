/**
 * Initializes the lock icon functionality once the DOM is fully loaded.
 * It sets the readonly state of input fields based on stored localStorage values,
 * and allows toggling the lock state when the associated lock icon is clicked.
 */
document.addEventListener("DOMContentLoaded", function () {
  /**
   * Selects all elements with the class "dbu-lock-icon" and sets up their behavior.
   * @type {NodeListOf<Element>}
   */
  var lockIcons = document.querySelectorAll(".dbu-lock-icon");

  lockIcons.forEach(function (icon) {
    /**
     * Retrieves the CSS selector from the "data-target" attribute of the lock icon,
     * which points to the associated input field.
     * @type {string}
     */
    var targetSelector = icon.getAttribute("data-target");

    /**
     * The input field element associated with this lock icon.
     * @type {HTMLElement|null}
     */
    var inputField = document.querySelector(targetSelector);

    if (inputField) {
      /**
       * A unique key used to store and retrieve the lock state in localStorage.
       * Constructed using the target selector (removing the '#' symbol).
       * @type {string}
       */
      var key = "dbu_lock_state_" + targetSelector.replace("#", "");

      /**
       * The stored state of the input field (either "locked" or "unlocked") retrieved from localStorage.
       * @type {string|null}
       */
      var storedState = localStorage.getItem(key);

      if (storedState === "unlocked") {
        inputField.removeAttribute("readonly");
        // When unlocked, show closed padlock to allow locking.
        icon.textContent = "🔒";
      } else {
        // Default state is locked.
        inputField.setAttribute("readonly", "readonly");
        // When locked, show open padlock to indicate it can be unlocked.
        icon.textContent = "🔓";
      }

      /**
       * Toggles the readonly state of the input field when the lock icon is clicked.
       * @param {Event} event - The click event object.
       */
      icon.addEventListener("click", function (event) {
        if (inputField.hasAttribute("readonly")) {
          // Currently locked: unlock the field.
          inputField.removeAttribute("readonly");
          icon.textContent = "🔒"; // Show closed padlock now that it's unlocked.
          localStorage.setItem(key, "unlocked");
        } else {
          // Currently unlocked: lock the field.
          inputField.setAttribute("readonly", "readonly");
          icon.textContent = "🔓"; // Show open padlock to indicate it's locked.
          localStorage.setItem(key, "locked");
        }
      });
    }
  });
});
