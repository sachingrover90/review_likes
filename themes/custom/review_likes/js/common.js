(function ($, Drupal) {  
  //fixed header logic
  if ($(window).width() > 992) {
      $(window).scroll(function() {
          if ($(this).scrollTop() > 1) {
              $("header").addClass("sticky");
          } else {
              $("header").removeClass("sticky");
          }
      });
  }

  //home page active class for main menu
  $(".main-navbar .nav-item .nav-link").on("click", function() {
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
  $(".like-btn").click(function() {
      $(this).find("img").toggleClass("liked");
  });

  //Add more button
  document.addEventListener("DOMContentLoaded", function() {
      var checkboxes = document.querySelectorAll(".checkboxsection .form-check");
      var moreLink = document.getElementById("toggleLink");

      if (checkboxes.length > 4) {
          // Hide extra checkboxes
          for (var i = 4; i < checkboxes.length; i++) {
              checkboxes[i].style.display = "none";
          }
          moreLink.style.display = "inline";
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


  //popup for product details screen

  $('.thumb-img').on('click', function() {
      const imgSrc = $(this).data('large');
      $('#mainImage').attr('src', imgSrc);
      $('.thumb-img').removeClass('active');
      $(this).addClass('active');
  });

  //Product images in product details page

  $(document).ready(function() {
      // Configure Lightbox
      lightbox.option({
          'resizeDuration': 200,
          'wrapAround': true,
          'albumLabel': "Image %1 of %2"
      });

      // Thumbnail switching
      $('.thumbnail').click(function() {
          $('.thumbnail').removeClass('active');
          $(this).addClass('active');

          const largeImageSrc = $(this).data('large');
          $('#mainProductImage').attr('src', largeImageSrc);
          $('#mainProductImage').parent().attr('href', largeImageSrc);
      });


      // Enter key for comment submission
      $('#comment').keypress(function(e) {
          if (e.which == 13) {
              $('.commentbox .btn').click();
          }
      });
  });

  function toggleDescription() {
      const shortDesc = document.getElementById('shortDesc');
      const fullDesc = document.getElementById('fullDesc');
      const toggleBtn = document.getElementById('toggleBtn');

      if (fullDesc.style.display === 'none') {
          shortDesc.style.display = 'none';
          fullDesc.style.display = 'inline';
          toggleBtn.textContent = 'Read Less';
      } else {
          shortDesc.style.display = 'inline';
          fullDesc.style.display = 'none';
          toggleBtn.textContent = 'Read More';
      }
  }

  function playVideo() {
      alert('Video playback would start here');
  }

  function showMoreImages() {
      // Trigger light
  }

  //slick slider in product description
  $(document).ready(function() {
      $('.screenshot-slider').slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: true,
          dots: false,
          infinite: false,
          adaptiveHeight: true,
          autoplay: false,
          responsive: [{
                  breakpoint: 768,
                  settings: {
                      slidesToShow: 2
                  }
              },
              {
                  breakpoint: 480,
                  settings: {
                      slidesToShow: 1
                  }
              }
          ]
      });
  });

  

  $('.send-button').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation(); // Prevents affecting parent elements like sliders

      const commentInput = $(this).closest('.commentbox').find('input');
      const comment = commentInput.val().trim();

      if (comment) {
          const newComment = $('<div class="cmnt">' + comment + '</div>');
          $('.comment-view-section').append(newComment);
          commentInput.val('');
      }
  });

  //other fileds show-hide
 $(document).ready(function () {
   function toggleFields() {
      var selected = $('#userinfo').val();
      if (selected === 'Other') {
         $('.other-only').show();
         $('.default-only').hide();
      } else {
         $('.other-only').hide();
         $('.default-only').show();
      }
   }

   toggleFields(); // On page load

   $('#userinfo').on('change', function () {
      toggleFields(); // On dropdown change
   });
});
})(jQuery, Drupal);