document.addEventListener('DOMContentLoaded', function() {
    // Увеличиваем счетчик просмотров
    if (typeof articleId !== 'undefined') {
        fetch(`/api/increment_views.php?id=${articleId}`)
            .catch(error => console.error('Ошибка при обновлении счетчика просмотров:', error));
    }

    // Инициализация галереи изображений
    const galleryImages = document.querySelectorAll('.gallery-image');
    galleryImages.forEach(img => {
        img.addEventListener('click', function() {
            // Здесь можно добавить открытие модального окна с увеличенным изображением
            console.log('Открываем изображение:', this.src);
        });
    });

    // Плавное появление элементов
    const animateElements = () => {
        const elements = document.querySelectorAll('.article-hero, .article-title, .article-content, .gallery-item');
        elements.forEach((el, index) => {
            setTimeout(() => {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, index * 100);
        });
    };

    // Добавляем стили для анимации
    const style = document.createElement('style');
    style.textContent = `
        .article-hero, .article-title, .article-content, .gallery-item {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
    `;
    document.head.appendChild(style);

    // Запускаем анимацию после загрузки страницы
    setTimeout(animateElements, 300);
});