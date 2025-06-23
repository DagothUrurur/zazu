$(document).ready(function () {
  initMobileMenu();
  $(window).on("resize", initMobileMenu);
  // Остальной код из DOMContentLoaded ...
  const header = document.querySelector(".header");
  window.addEventListener("scroll", function () {
    if (window.scrollY > 50) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  });

  // Случайный совет при загрузке
  const tips = [
    "Нажмите 'B' для кисти — если осмелитесь.",
    "Сохраняйтесь. Последний автосохранённый файл был 3 часа назад.",
    "Настоящий чёрный — это #0A0A0A. #000000 — слишком очевидно.",
    "Ваш курсор иногда будет дрожать. Это не баг, а особенность.",
    "Если экран мерцает — отойдите от монитора.",
  ];

  const randomTip = tips[Math.floor(Math.random() * tips.length)];
  console.log(`Совет дня: ${randomTip}`);

  // Анимация силуэтов при движении мыши
  const silhouettes = document.querySelectorAll(".silhouette");
  document.addEventListener("mousemove", function (e) {
    const x = e.clientX / window.innerWidth;
    const y = e.clientY / window.innerHeight;

    silhouettes.forEach((sil, index) => {
      const moveX = (x - 0.5) * 20 * (index + 1);
      const moveY = (y - 0.5) * 10 * (index + 1);
      sil.style.transform = `translate(${moveX}px, ${moveY}px)`;
    });
  });

  // Эффект при наведении на карточки
  const cards = document.querySelectorAll(
    ".start-card, .article-card, .artwork-card"
  );
  cards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-5px)";
      this.style.boxShadow = "0 10px 20px rgba(139, 0, 0, 0.3)";
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)";
      this.style.boxShadow = "none";
    });
  });

  // Эффект глитча для работ
  const artworks = document.querySelectorAll(".artwork-card");
  artworks.forEach((art) => {
    art.addEventListener("mouseenter", function () {
      const glitch = this.querySelector(".artwork-glitch");
      glitch.style.opacity = "0.3";
      glitch.style.animation = "glitch-effect 0.5s infinite";
    });

    art.addEventListener("mouseleave", function () {
      const glitch = this.querySelector(".artwork-glitch");
      glitch.style.opacity = "0";
      glitch.style.animation = "none";
    });
  });

  // Анимация капель крови на кнопке CTA
  const ctaBtn = document.querySelector(".cta-btn");
  if (ctaBtn) {
    ctaBtn.addEventListener("mouseenter", function () {
      const bloodDrip = this.querySelector(".blood-drip");
      bloodDrip.style.animation = "blood-drip 1s infinite";
    });

    ctaBtn.addEventListener("mouseleave", function () {
      const bloodDrip = this.querySelector(".blood-drip");
      bloodDrip.style.animation = "none";
    });
  }

  // Случайное мерцание элементов
  setInterval(function () {
    const flickerElements = document.querySelectorAll(
      ".banner-title, .icon-halo"
    );
    flickerElements.forEach((el) => {
      if (Math.random() > 0.7) {
        el.style.opacity = "0.7";
        setTimeout(() => {
          el.style.opacity = "1";
        }, 100);
      }
    });
  }, 3000);
});

function getParameterByName(name, url = window.location.href) {
  name = name.replace(/[\[\]]/g, "\\$&");
  const regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)");
  const results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return "";
  return decodeURIComponent(results[2].replace(/\+/g, " "));
}
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
