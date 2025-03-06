
var ads_acc = document.getElementsByClassName("ads_accordion");
var i;

for (i = 0; i < ads_acc.length; i++) {
  ads_acc[i].addEventListener("click", function() {
    // Close all open panels
    closeAllPanels();

    /* Toggle between adding and removing the "active" class,
    to highlight the button that controls the panel */
    this.classList.toggle("active");

    /* Toggle between hiding and showing the active panel */
    var panel = this.nextElementSibling;
    if (panel.style.display === "block") {
      panel.style.display = "none";
    } else {
      panel.style.display = "block";
    }
  });
}

function closeAllPanels() {
  var panels = document.getElementsByClassName("panel");
  for (var j = 0; j < panels.length; j++) {
    panels[j].style.display = "none";
    var accordionButtons = document.getElementsByClassName("ads_accordion");
    accordionButtons[j].classList.remove("active");
  }
}
