  (function ($) {
  if ($(window).width() > 992) {
    $(window).scroll(function () {
      if ($(this).scrollTop() > 1) {
        $("header").addClass("sticky");
      } else {
        $("header").removeClass("sticky");
      }
    });
  }
  
  //home page active class for main menu
  $(".main-navbar .nav-item .nav-link").on("click", function () {
    $(".main-navbar .nav-item .nav-link").removeClass("active");
    $(this).addClass("active");
  });

//close post
function closeCard() {
  document.getElementById("myCard").style.display = "none";
}
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
//like script
$(".like-btn").click(function () {
  $(this).find("img").toggleClass("liked");
});

//Add more button
document.addEventListener("DOMContentLoaded", function () {
    var checkboxes = document.querySelectorAll(".checkboxsection .form-check");
    var moreLink = document.getElementById("toggleLink");

    if (checkboxes.length > 4) {
        // Hide extra checkboxes
        for (var i = 4; i < checkboxes.length; i++) {
            checkboxes[i].style.display = "none";
        }
        moreLink.style.display = "inline"; // Show More link
    }
});

function toggleCategories() {
    var checkboxes = document.querySelectorAll(".checkboxsection .form-check");
    var moreLink = document.getElementById("toggleLink");

    var isExpanded = moreLink.innerText === "Less";
    for (var i = 4; i < checkboxes.length; i++) {
        checkboxes[i].style.display = isExpanded ? "none" : "block";
    }
    
    moreLink.innerText = isExpanded ? "More" : "Less";
}
})(jQuery);

