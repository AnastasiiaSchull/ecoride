document.addEventListener("DOMContentLoaded", function () {
  const toggles = document.querySelectorAll(".icon-toggle");

  toggles.forEach(toggle => {
    toggle.addEventListener("click", () => {
      const targetId = toggle.getAttribute("data-target");
      const candidates = [
        document.getElementById(targetId),
        document.getElementById(targetId.replace("select-", "")),
        document.querySelector(`select[name="${targetId.replace("select-", "")}"]`)
      ];

      const targetSelect = candidates.find(el => el !== null);
      if (!targetSelect) return;

      // show only the required one, leave other selects untouched
      targetSelect.classList.remove("hide");
      targetSelect.classList.add("show");

      // activate the required radio button
      if (targetId.includes("depart")) {
        document.getElementById("radio-depart").checked = true;
      } else if (targetId.includes("destination")) {
        document.getElementById("radio-destination").checked = true;
      }
    });
  });
});
