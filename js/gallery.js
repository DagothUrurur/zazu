document.addEventListener('DOMContentLoaded', function() {
    // Таймер конкурса
    function updateContestTimer() {
        const endDate = new Date();
        endDate.setDate(endDate.getDate() + 3); // Конкурс заканчивается через 3 дня
        endDate.setHours(23, 59, 59, 0);
        
        const now = new Date();
        const diff = endDate - now;
        
        if (diff <= 0) {
            document.querySelector('.contest-timer').innerHTML = '<i class="fas fa-hourglass-end"></i> Конкурс завершён!';
            return;
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        const timerElement = document.querySelector('.contest-timer');
        timerElement.innerHTML = `<i class="fas fa-hourglass-half"></i> До конца: ${days}д ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    
    // Обновляем таймер каждую секунду
    updateContestTimer();
    setInterval(updateContestTimer, 1000);
    
    // Голосование за работы
    const voteButtons = document.querySelectorAll('.vote-btn');
    voteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const artworkCard = this.closest('.contest-artwork');
            const votesCount = artworkCard.querySelector('.votes');
            
            // Анимация
            this.innerHTML = '<i class="fas fa-check"></i> Ваш голос учтён';
            this.style.backgroundColor = 'var(--gold-dark)';
            
            // Увеличиваем счётчик
            let currentVotes = parseInt(votesCount.textContent);
            votesCount.textContent = currentVotes + 1;
            
            // Блокируем кнопку
            this.disabled = true;
            
            // Эффект "пульсации" на карточке
            artworkCard.style.boxShadow = '0 0 20px rgba(255, 215, 0, 0.5)';
            setTimeout(() => {
                artworkCard.style.boxShadow = 'none';
            }, 1000);
        });
    });
    
    // Лайки для обычных работ
    const likeButtons = document.querySelectorAll('.like-btn');
    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const likesCount = this.closest('.artwork-actions').querySelector('.likes'); // FIXED: Вынесено за условие
            
            if (this.querySelector('i').classList.contains('far')) {
                // Анимация лайка
                this.innerHTML = '<i class="fas fa-heart"></i>';
                this.style.color = 'var(--blood)';
                
                // Увеличиваем счётчик
                let currentLikes = parseInt(likesCount.textContent);
                likesCount.textContent = currentLikes + 1;
            } else {
                // Убираем лайк
                this.innerHTML = '<i class="far fa-heart"></i>';
                this.style.color = '';
                
                let currentLikes = parseInt(likesCount.textContent);
                likesCount.textContent = currentLikes - 1;
            }
        });
    });
    
    // Открытие модального окна с комментариями
    const commentButtons = document.querySelectorAll('.comment-btn, .expand-btn, .view-btn');
    commentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const artworkCard = this.closest('.artwork-card, .contest-artwork');
            const title = artworkCard.querySelector('h3').textContent;
            const author = artworkCard.querySelector('p').textContent.replace('Автор: ', '');
            const votes = artworkCard.querySelector('.votes, .likes').textContent;
            const imageSrc = artworkCard.querySelector('img').src;
            
            const modal = document.querySelector('#artworkModal');
            modal.querySelector('.modal-title').innerHTML = `"${title}" <span class="author">@${author}</span>`;
            modal.querySelector('.main-artwork').src = imageSrc;
            modal.querySelector('.tool-btn i').nextSibling.textContent = ` ${votes}`;
        });
    });
    
    // Отправка комментария
    const commentForm = document.querySelector('.comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const commentInput = this.querySelector('.comment-input');
            const commentText = commentInput.value.trim();
            
            if (commentText) {
                const commentsList = document.querySelector('.comments-list');
                
                // Создаём новый комментарий
                const newComment = document.createElement('div');
                newComment.className = 'comment';
                newComment.innerHTML = `
                    <div class="comment-author">
                        <img src="img/avatars/default.jpg" alt="Аватар" class="comment-avatar">
                        <span class="comment-username">@Вы</span>
                        <span class="comment-date">только что</span>
                    </div>
                    <div class="comment-text">${commentText}</div>
                    <div class="comment-actions">
                        <button class="comment-like"><i class="far fa-heart"></i> 0</button>
                        <button class="comment-reply">Ответить</button>
                    </div>
                `;
                
                // Добавляем в начало списка
                commentsList.insertBefore(newComment, commentsList.firstChild);
                commentInput.value = '';
                newComment.style.animation = 'fadeIn 0.5s';
            }
        });
    }
    
    // Лайки для комментариев
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('comment-like')) { // FIXED: Добавлена закрывающая скобка
            const likeBtn = e.target;
            const icon = likeBtn.querySelector('i');
            const countText = likeBtn.textContent.trim();
            let count = parseInt(countText.replace(/\D/g, '')) || 0; // FIXED: Улучшен парсинг числа
            
            if (icon.classList.contains('far')) {
                icon.className = 'fas fa-heart';
                likeBtn.innerHTML = `<i class="fas fa-heart"></i> ${count + 1}`;
            } else {
                icon.className = 'far fa-heart';
                likeBtn.innerHTML = `<i class="far fa-heart"></i> ${count - 1}`;
            }
        }
    });
    
    // Загрузка работы на конкурс
    const submitForm = document.querySelector('.submit-form');
    if (submitForm) {
        submitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitModalEl = document.querySelector('#submitModal');
            const submitModal = bootstrap.Modal.getInstance(submitModalEl) || new bootstrap.Modal(submitModalEl); // FIXED: Проверка на существующий инстанс
            
            const submitBtn = this.querySelector('.submit-btn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Работа отправлена!';
                setTimeout(() => {
                    submitModal.hide();
                    submitBtn.innerHTML = '<i class="fas fa-upload"></i> Принести Жертву Искусству';
                    submitBtn.disabled = false;
                    this.reset();
                    alert('Ваше видение было успешно добавлено в галерею!');
                }, 1000);
            }, 2000);
        });
    }
    
    // Случайное мерцание элементов
    setInterval(function() {
        document.querySelectorAll('.contest-badge, .artwork-glitch').forEach(el => {
            if (Math.random() > 0.9) {
                el.style.opacity = '0.5';
                setTimeout(() => el.style.opacity = '0.2', 100);
            }
        });
    }, 3000);
    
    // Эффект при наведении на карточки
    document.querySelectorAll('.artwork-card, .contest-artwork').forEach(card => {
        card.addEventListener('mouseenter', function() {
            const glitch = this.querySelector('.artwork-glitch');
            if (glitch) {
                glitch.style.opacity = '0.2';
                glitch.style.animation = 'glitch-effect 0.5s infinite';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            const glitch = this.querySelector('.artwork-glitch');
            if (glitch) {
                glitch.style.opacity = '0';
                glitch.style.animation = 'none';
            }
        });
    });
    
    // Инициализация модальных окон
    document.querySelectorAll('.modal').forEach(modalEl => {
        modalEl.addEventListener('shown.bs.modal', function() {
            if (this.id === 'artworkModal') {
                const commentInput = this.querySelector('.comment-input');
                if (commentInput) commentInput.focus();
            }
        });
    });
});

// Анимация для новых комментариев
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;
document.head.appendChild(style);