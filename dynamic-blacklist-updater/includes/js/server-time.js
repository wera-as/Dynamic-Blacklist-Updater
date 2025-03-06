/**
 * Immediately Invoked Function Expression (IIFE) to update the server time every second.
 */
(function () {
  /**
   * The DOM element that displays the server time.
   * @type {HTMLElement}
   */
  var serverTimeElem = document.getElementById("server-time");

  /**
   * The initial server timestamp (in seconds) obtained from a data attribute.
   * @type {number}
   */
  var initialServerTimestamp = parseInt(
    serverTimeElem.getAttribute("data-server-timestamp"),
    10
  );

  /**
   * The time (in milliseconds) when the client loaded the page.
   * @type {number}
   */
  var clientLoadTime = Date.now();

  /**
   * Array containing the full names of the months.
   * @type {string[]}
   */
  var months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ];

  /**
   * Pads the given number with a leading zero if it is less than 10.
   *
   * @param {number} number - The number to pad.
   * @returns {string} The padded number as a string.
   */
  function pad(number) {
    return (number < 10 ? "0" : "") + number;
  }

  /**
   * Updates the innerHTML of the server time element with the current server time.
   * The current server time is calculated based on the initial server timestamp and the elapsed time since the client loaded the page.
   */
  function updateServerTime() {
    // Calculate elapsed seconds since page load.
    var elapsedSeconds = Math.floor((Date.now() - clientLoadTime) / 1000);
    // Compute the current server timestamp.
    var currentServerTimestamp = initialServerTimestamp + elapsedSeconds;
    var currentServerDate = new Date(currentServerTimestamp * 1000);

    var day = currentServerDate.getDate(); // No leading zero.
    var month = months[currentServerDate.getMonth()];
    var year = currentServerDate.getFullYear();
    var hours = pad(currentServerDate.getHours());
    var minutes = pad(currentServerDate.getMinutes());
    var seconds = pad(currentServerDate.getSeconds());

    var formattedTime =
      day +
      ". " +
      month +
      ", " +
      year +
      " " +
      hours +
      ":" +
      minutes +
      ":" +
      seconds;
    serverTimeElem.innerHTML = formattedTime;
  }

  // Update the server time every second.
  setInterval(updateServerTime, 1000);
})();
