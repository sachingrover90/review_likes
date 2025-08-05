//more option
function toggleDescription() {
  var shortDesc = document.getElementById("shortDesc");
  var fullDesc = document.getElementById("fullDesc");
  var toggleBtn = document.getElementById("toggleBtn");

  if (fullDesc.style.display === "none") {
    shortDesc.style.display = "none";
    fullDesc.style.display = "inline";
    toggleBtn.textContent = "Read Less";
  } else {
    shortDesc.style.display = "inline";
    fullDesc.style.display = "none";
    toggleBtn.textContent = "Read More";
  }
}