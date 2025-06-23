$(document).ready(function () {
  initMobileMenu();
  $(window).on("resize", initMobileMenu);
  const header = document.querySelector(".header");
  window.addEventListener("scroll", function () {
    if (window.scrollY > 50) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  });
});
function initMobileMenu() {
  console.log("initMobileMenu called");
  console.log("Burger exists:", $(".burger-menu").length);
  console.log("Mobile menu exists:", $("#mobileMenuContainer").length);
  const $burger = $(".burger-menu");
  const $mobileMenu = $("#mobileMenuContainer");
  let isAnimating = false;

  // Сброс состояния
  function resetMenu() {
    $burger.removeClass("active");
    $mobileMenu.removeClass("active").hide();
    $("body").removeClass("menu-open");
    isAnimating = false;
  }

  // Только для мобильных
  if ($(window).width() > 992) {
    resetMenu();
    $burger.hide();
    return;
  }

  $burger
    .show()
    .off("click")
    .on("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      if (isAnimating) return;
      isAnimating = true;

      const willOpen = !$(this).hasClass("active");

      if (willOpen) {
        $mobileMenu.stop(true, true).slideDown(300, function () {
          isAnimating = false;
        });
      } else {
        $mobileMenu.stop(true, true).slideUp(300, function () {
          isAnimating = false;
        });
      }

      $(this).toggleClass("active");
      $mobileMenu.toggleClass("active");
      $("body").toggleClass("menu-open", willOpen);
    });

  // Обработчики закрытия
  $mobileMenu.on("click", "a", function (e) {
    if (!$(this).attr("href").startsWith("#")) {
      e.preventDefault();
      resetMenu();
      setTimeout(() => {
        window.location.href = $(this).attr("href");
      }, 300);
    }
  });

  $(document).on("click", function (e) {
    if (
      $mobileMenu.is(":visible") &&
      !$(e.target).closest(".burger-menu").length &&
      !$(e.target).closest("#mobileMenuContainer").length
    ) {
      resetMenu();
    }
  });

  $(document).on("keyup", function (e) {
    if (e.key === "Escape" && $mobileMenu.is(":visible")) {
      resetMenu();
    }
  });
}
