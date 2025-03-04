document.addEventListener("DOMContentLoaded", function () {
  const checkbox = document.getElementById("show-blacklist");
  const textarea = document.getElementById("blacklist-textarea");

  checkbox.addEventListener("change", function () {
    textarea.style.display = this.checked ? "block" : "none";
  });
});
