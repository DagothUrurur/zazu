
$(document).ready(function() {
    // 1. Форматирование времени
    function formatTime(seconds) {
        if (seconds <= 0) return '00:00:00';
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
// Для менюшки
$(window).on('pageshow', function() {
    initMobileMenu();
});
// Для времени конкурса

function formatTime(seconds) {
    if (seconds <= 0) return '00:00:00';
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

    function updateLikeButton(btn, count, isLiked) {
        btn.toggleClass('liked', isLiked)
           .find('i')
           .removeClass(isLiked ? 'far' : 'fas')
           .addClass(isLiked ? 'fas' : 'far')
           .end()
           .find('.like-count')
           .text(count);
    }

    function loadComments(artworkId) {
        $.get('php/get_comments.php?artwork_id=' + artworkId, function(data) {
            $('#commentsList').html(data);
        });
    }

    // 2. Упрощенная функция обновления таймера
function updateContestTimer() {
    const now = Math.floor(Date.now() / 1000);
    const desktopTimer = $('#contestTimerDesktop');
    const mobileTimer = $('#contestTimerMobile');
    
    if (contestStart && contestEnd) {
        if (now < contestStart) {
            // До начала конкурса
            const diff = contestStart - now;
            const timeStr = formatTime(diff);
            
            desktopTimer.html(`<i class="fas fa-hourglass-start"></i> До начала: <span class="time-remaining">${timeStr}</span>`);
            mobileTimer.html(`<i class="fas fa-hourglass-start"></i> Начало через: ${formatMobileTime(diff)}`);
        } 
        else if (now < contestEnd) {
            // Конкурс идёт
            const diff = contestEnd - now;
            const timeStr = formatTime(diff);
            
            desktopTimer.html(`<i class="fas fa-hourglass-half"></i> До конца: <span class="time-remaining">${timeStr}</span>`);
            mobileTimer.html(`<i class="fas fa-hourglass-half"></i> Осталось: ${formatMobileTime(diff)}`);
            
            // Добавляем анимацию, если осталось меньше часа
            if (diff < 3600) {
                mobileTimer.addClass('urgent');
            } else {
                mobileTimer.removeClass('urgent');
            }
        } 
        else {
            // Конкурс завершён
            desktopTimer.html('<i class="fas fa-hourglass-end"></i> Конкурс завершён');
            mobileTimer.html('<i class="fas fa-hourglass-end"></i> Голосование закрыто');
            mobileTimer.removeClass('urgent');
        }
        
        setTimeout(updateContestTimer, 1000);
    }
}

// Специальный формат для мобильных
function formatMobileTime(seconds) {
    if (seconds <= 0) return '00:00:00';
    
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    if (days > 0) {
        return `${days}д ${hours}ч`;
    } else if (hours > 0) {
        return `${hours}ч ${mins}м`;
    } else if (mins > 0) {
        return `${mins}м ${secs}с`;
    } else {
        return `${secs}с`;
    }
}
    // Запускаем таймер сразу после загрузки страницы
    updateContestTimer();

    // 3. Обработка лайков
    $(document).on('click', '.like-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const btn = $(this);
        const artworkId = btn.data('artwork-id');
        
        if(!artworkId) {
            console.error('Artwork ID not found');
            return;
        }
        
        $.post('php/like.php', { artwork_id: artworkId }, function(response) {
            if(response.status === 'success') {
                updateLikeButton(btn, response.count, response.action === 'added');
                $(`.like-btn[data-artwork-id="${artworkId}"]`).each(function() {
                    updateLikeButton($(this), response.count, response.action === 'added');
                });
            } else if(response.error === 'auth') {
                alert('Для выполнения этого действия необходимо авторизоваться');
                window.location.href = '/auth/login.php';
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
        });
    });
    
// Обработчик модального окна
$('#artworkModal').on('show.bs.modal', function(event) {
    const button = $(event.relatedTarget);
    const artworkId = button.data('id');
    const isContest = button.data('contest') || false;
    const modal = $(this);

    modal.data('artwork-id', artworkId);
    
    // Загрузка данных работы
    $.get('php/get_artwork.php?id=' + artworkId, function(artwork) {
        // Обновляем основную информацию
        modal.find('#artworkModalTitle').text(artwork.title + ' @' + artwork.author);
        modal.find('#modalArtworkImage').attr('src', '/uploads/artworks/' + artwork.image_path);
        modal.find('#viewsCount').text(artwork.views);
        
        // Обновляем лайк
        const likeBtn = modal.find('#modalLikeBtn')
            .data('artwork-id', artwork.id)
            .toggleClass('liked', artwork.is_liked)
            .find('i')
            .removeClass(artwork.is_liked ? 'far' : 'fas')
            .addClass(artwork.is_liked ? 'fas' : 'far')
            .end()
            .find('.like-count')
            .text(artwork.like_count);
        
       // Для конкурсных работ
if (isContest) {
    const voteBtn = modal.find('#modalVoteBtn')
        .removeClass('d-none')
        .data('artwork-id', artworkId);
    
    // Сначала ставим данные из artwork
    updateVoteButton(voteBtn, artwork.votes_count || 0, artwork.user_has_voted);
    
    // Затем проверяем актуальное состояние
    checkAndUpdateVote(artworkId, voteBtn);
} else {
    modal.find('#modalVoteBtn').addClass('d-none');
}
        
        // Загружаем комментарии
        loadComments(artworkId);
        
    }, 'json');
});

// Обработчик голосования в модальном окне
$(document).on('click', '#modalVoteBtn:not(.processing)', function(e) {
    e.preventDefault();
    const btn = $(this);
    const artworkId = btn.data('artwork-id');
    
    if(!artworkId) return;
    btn.addClass('processing');
    
    $.post('/php/vote.php', { artwork_id: artworkId }, function(response) {
        if(response.status === 'success') {
            // Обновляем кнопку в модальном окне
            btn.find('.vote-count').text(response.count);
            btn.toggleClass('voted', response.action === 'added')
               .find('i')
               .removeClass(response.action === 'added' ? 'far' : 'fas')
               .addClass(response.action === 'added' ? 'fas' : 'far');
            
            // Обновляем кнопки вне модального окна
            $(`.vote-btn[data-artwork-id="${artworkId}"]`).each(function() {
                $(this).find('.vote-count').text(response.count);
                $(this).toggleClass('voted', response.action === 'added')
                      .find('i')
                      .removeClass(response.action === 'added' ? 'far' : 'fas')
                      .addClass(response.action === 'added' ? 'fas' : 'far');
            });
        }
    }, 'json')
    .always(() => btn.removeClass('processing'));
});
    // Функция для обновления кнопки голосования
function updateVoteButton(btn, count, isVoted) {
    btn.find('.vote-count').text(count);
    btn.toggleClass('voted', isVoted)
       .find('i')
       .removeClass(isVoted ? 'far' : 'fas')
       .addClass(isVoted ? 'fas' : 'far');
}
function checkAndUpdateVote(artworkId, button) {
    $.get('/php/get_vote_status.php', { artwork_id: artworkId }, function(response) {
        if(response.status === 'success') {
            updateVoteButton(button, response.count, response.hasVoted);
        }
    }, 'json');
}
    // Обработчик голосования (вне модального окна)
    $(document).on('click', '.vote-btn:not(.disabled)', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const btn = $(this);
        const artworkId = btn.data('artwork-id');
        
        if(!artworkId || btn.hasClass('processing')) return;
        btn.addClass('processing');
        
        $.post('/php/vote.php', { artwork_id: artworkId }, function(response) {
            if(response.status === 'success') {
                // Обновляем все кнопки голосования для этой работы
                $(`.vote-btn[data-artwork-id="${artworkId}"]`).each(function() {
                    updateVoteButton($(this), response.count, response.action === 'added');
                });
            }
        }, 'json')
        .always(() => btn.removeClass('processing'));
    });

    // Обработчик голосования в модальном окне
    $(document).on('click', '#modalVoteBtn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const artworkId = btn.data('artwork-id');
        
        if(!artworkId || btn.hasClass('processing')) return;
        btn.addClass('processing');
        
        $.post('/php/vote.php', { artwork_id: artworkId }, function(response) {
            if(response.status === 'success') {
                // Обновляем кнопку в модальном окне
                btn.find('.vote-count').text(response.count);
                btn.toggleClass('voted', response.action === 'added')
                   .find('i')
                   .removeClass(response.action === 'added' ? 'far' : 'fas')
                   .addClass(response.action === 'added' ? 'fas' : 'far');
                
                // Обновляем кнопки вне модального окна
                $(`.vote-btn[data-artwork-id="${artworkId}"]`).each(function() {
                    $(this).find('.vote-count').text(response.count);
                    $(this).toggleClass('voted', response.action === 'added')
                          .find('i')
                          .removeClass(response.action === 'added' ? 'far' : 'fas')
                          .addClass(response.action === 'added' ? 'fas' : 'far');
                });
            }
        }, 'json')
        .always(() => btn.removeClass('processing'));
    });
    
    // 6. Обработка комментариев
    $('.comment-submit').click(function() {
        const artworkId = $('#artworkModal').data('artwork-id');
        const content = $('#commentText').val().trim();
        
        if(!content) return;
        
        $.post('php/add_comment.php', {
            artwork_id: artworkId,
            content: content
        }, function(response) {
            if(response.status === 'success') {
                $('#commentText').val('');
                loadComments(artworkId);
            }
        }, 'json');
    });
// Обработчик изменения сортировки
$('#sort').change(function() {
    const sortValue = $(this).val();
    const currentPage = getParameterByName('page') || 1;
    window.location.href = `?page=${currentPage}&sort=${sortValue}`;
});

// Функция для получения параметров из URL
function getParameterByName(name, url = window.location.href) {
    name = name.replace(/[\[\]]/g, '\\$&');
    const regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
    const results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

// Установка выбранного значения сортировки при загрузке
$(document).ready(function() {
    const currentSort = getParameterByName('sort') || 'newest';
    $('#sort').val(currentSort);
});
function initMobileMenu() {
    const $burger = $('.burger-menu');
    const $mobileMenu = $('#mobileMenuContainer');
    let isAnimating = false;

    // Сброс состояния
    function resetMenu() {
        $burger.removeClass('active');
        $mobileMenu.removeClass('active').hide();
        $('body').removeClass('menu-open');
        isAnimating = false;
    }

    // Только для мобильных
    if ($(window).width() > 992) {
        resetMenu();
        $burger.hide();
        return;
    }

    $burger.show().off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (isAnimating) return;
        isAnimating = true;
        
        const willOpen = !$(this).hasClass('active');
        
        if (willOpen) {
            $mobileMenu.stop(true, true).slideDown(300, function() {
                isAnimating = false;
            });
        } else {
            $mobileMenu.stop(true, true).slideUp(300, function() {
                isAnimating = false;
            });
        }
        
        $(this).toggleClass('active');
        $mobileMenu.toggleClass('active');
        $('body').toggleClass('menu-open', willOpen);
    });

    // Обработчики закрытия
    $mobileMenu.on('click', 'a', function(e) {
        if (!$(this).attr('href').startsWith('#')) {
            e.preventDefault();
            resetMenu();
            setTimeout(() => {
                window.location.href = $(this).attr('href');
            }, 300);
        }
    });

    $(document).on('click', function(e) {
        if ($mobileMenu.is(':visible') && 
            !$(e.target).closest('.burger-menu').length && 
            !$(e.target).closest('#mobileMenuContainer').length) {
            resetMenu();
        }
    });

    $(document).on('keyup', function(e) {
        if (e.key === "Escape" && $mobileMenu.is(':visible')) {
            resetMenu();
        }
    });
}
    
    // Таймер конкурса для мобильных
    function adaptContestTimer() {
        if ($(window).width() > 768) return;
        
        const $timeElement = $('.contest-timer .time-remaining');
        if (!$timeElement.length) return;
        
        const timeParts = $timeElement.text().split(':');
        if (timeParts.length === 3) {
            const hours = parseInt(timeParts[0]);
            const days = Math.floor(hours / 24);
            
            if (days >= 1) {
                $timeElement.text(`${days}д ${hours%24}ч`);
            } else {
                $timeElement.text(`${timeParts[0]}ч ${timeParts[1]}м`);
            }
        }
    }
    
    // Инициализация
    initMobileMenu();
    adaptContestTimer();
    
    // Реинициализация при изменении размера
    $(window).on('resize', function() {
        initMobileMenu();
        adaptContestTimer();
    });
});