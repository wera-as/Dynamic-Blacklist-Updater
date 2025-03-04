(function () {
  var serverTimeElem = document.getElementById("server-time");
  var initialServerTimestamp = parseInt(
    serverTimeElem.getAttribute("data-server-timestamp"),
    10
  );
  var clientLoadTime = Date.now();

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

  // Pad hours, minutes, and seconds to 2 digits.
  function pad(number) {
    return (number < 10 ? "0" : "") + number;
  }

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

  // Update every second.
  setInterval(updateServerTime, 1000);
})();
