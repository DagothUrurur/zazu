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
});
document.addEventListener("DOMContentLoaded", function () {
  // Фильтрация по категориям с обновлением URL
  const categoryTabs = document.querySelectorAll(".category-tab");
  const archiveCards = document.querySelectorAll(".archives-grid > div");

  categoryTabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      // Удаляем активный класс у всех вкладок
      categoryTabs.forEach((t) => t.classList.remove("active"));
      // Добавляем активный класс текущей вкладке
      this.classList.add("active");

      const category = this.dataset.category;

      // Обновляем URL без перезагрузки страницы
      const url = new URL(window.location.href);
      if (category === "all") {
        url.searchParams.delete("category");
      } else {
        url.searchParams.set("category", category);
      }
      history.pushState(null, "", url.toString());

      // Показываем/скрываем карточки в зависимости от категории
      archiveCards.forEach((card) => {
        if (category === "all" || card.dataset.category === category) {
          card.style.display = "block";
        } else {
          card.style.display = "none";
        }
      });
    });
  });

  // При загрузке страницы активируем соответствующий таб из URL
  const urlParams = new URLSearchParams(window.location.search);
  const categoryParam = urlParams.get("category");
  if (categoryParam) {
    const activeTab = document.querySelector(
      `.category-tab[data-category="${categoryParam}"]`
    );
    if (activeTab) {
      activeTab.click();
    }
  }

  // Анимация карточек при наведении
  archiveCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      const archiveCard = this.querySelector(".archive-card");
      if (archiveCard) {
        archiveCard.style.transform = "translateY(-5px)";
        archiveCard.style.boxShadow = "0 10px 20px rgba(139, 0, 0, 0.3)";
      }
    });

    card.addEventListener("mouseleave", function () {
      const archiveCard = this.querySelector(".archive-card");
      if (archiveCard) {
        archiveCard.style.transform = "translateY(0)";
        archiveCard.style.boxShadow = "none";
      }
    });
  });

  // Эффект при наведении на ссылки
  const cardLinks = document.querySelectorAll(".card-link");
  cardLinks.forEach((link) => {
    link.addEventListener("mouseenter", function () {
      const icon = this.querySelector("i");
      if (icon) {
        icon.style.transform = "translateX(3px)";
      }
    });

    link.addEventListener("mouseleave", function () {
      const icon = this.querySelector("i");
      if (icon) {
        icon.style.transform = "translateX(0)";
      }
    });
  });

  // Случайное мерцание элементов
  setInterval(function () {
    const elements = document.querySelectorAll(".archives-title, .card-badge");
    elements.forEach((el) => {
      if (Math.random() > 0.7) {
        el.style.opacity = "0.7";
        setTimeout(() => {
          el.style.opacity = "1";
        }, 100);
      }
    });
  }, 3000);

  // Эффект для поисковой строки
  const searchInput = document.querySelector(".search-box input");
  if (searchInput) {
    searchInput.addEventListener("focus", function () {
      const icon = this.parentElement.querySelector("i");
      if (icon) {
        icon.style.color = "var(--gold)";
      }
    });

    searchInput.addEventListener("blur", function () {
      const icon = this.parentElement.querySelector("i");
      if (icon) {
        icon.style.color = "var(--parchment-dark)";
      }
    });
  }
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
