$(document).ready(function() {
    // 1. Обработчик создания конкурса
    $('#createContestModal').on('shown.bs.modal', function() {
        $('#saveContestBtn').off('click').on('click', function(e) {
            e.preventDefault();
            
            if (!$('#createContestForm')[0].checkValidity()) {
                $('#createContestForm').addClass('was-validated');
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin"></i> Создание...');

            $.ajax({
                url: '../admin/php/create_contest.php',
                type: 'POST',
                data: $('#createContestForm').serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#createContestModal').modal('hide');
                        location.reload();
                    } else {
                        alert("Ошибка: " + (response.message || 'Неизвестная ошибка'));
                    }
                },
                error: function(xhr) {
                    alert("Ошибка сервера: " + xhr.statusText);
                },
                complete: function() {
                    btn.prop('disabled', false).html('Создать конкурс');
                }
            });
        });
    });

    // 2. Обработчик кнопки редактирования
    $(document).on('click', '.edit-contest-btn', function() {
        const contestId = $(this).data('contest-id');
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '../admin/php/get_contest.php',
            type: 'GET',
            data: { id: contestId },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    $('#editContestModal #contestId').val(response.data.id);
                    $('#editContestModal #editTitle').val(response.data.title || '');
                    $('#editContestModal #editDescription').val(response.data.description || '');
                    
                    const formatDate = (dateStr) => {
                        if (!dateStr) return '';
                        return dateStr.includes('T') ? dateStr.substring(0, 16) 
                            : dateStr.replace(' ', 'T').substring(0, 16);
                    };
                    
                    $('#editContestModal #editStartDate').val(formatDate(response.data.start_date));
                    $('#editContestModal #editEndDate').val(formatDate(response.data.end_date));
                    $('#editContestModal').modal('show');
                } else {
                    alert(response.message || "Неизвестная ошибка сервера");
                }
            },
            error: function(xhr) {
                alert("Ошибка сервера: " + xhr.statusText);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-pencil-alt"></i>');
            }
        });
    });

    // 3. Обработчик сохранения изменений
    $('#updateContestBtn').click(function() {
        const form = $('#editContestForm');
        
        if (!form[0].checkValidity()) {
            form.addClass('was-validated');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true)
           .html('<i class="fas fa-spinner fa-spin"></i> Сохранение...');

        const formData = {
            id: form.find('#contestId').val(),
            title: form.find('#editTitle').val(),
            description: form.find('#editDescription').val(),
            start_date: new Date(form.find('#editStartDate').val()).toISOString().slice(0, 19).replace('T', ' '),
            end_date: new Date(form.find('#editEndDate').val()).toISOString().slice(0, 19).replace('T', ' ')
        };

        $.ajax({
            url: '../admin/php/update_contest.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    $('#editContestModal').modal('hide');
                    location.reload();
                } else {
                    alert('Ошибка: ' + (response.message || 'Неизвестная ошибка'));
                }
            },
            error: function(xhr) {
                alert('Ошибка сервера: ' + xhr.statusText);
            },
            complete: function() {
                btn.prop('disabled', false).html('Сохранить');
            }
        });
    });

    // 4. Обработчик выбора победителя
    $(document).on('click', '.select-winner-btn', function() {
        const contestId = $(this).data('contest-id');
        const btn = $(this);
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $('#winnerSelectionProgress').show();
        $('#contestArtworksContainer').hide();
        
        $('#selectWinnerModal').modal('show');
        
        $.get('../admin/php/get_contest_artworks.php', { 
            contest_id: contestId 
        }, function(response) {
            $('#winnerSelectionProgress').hide();
            
            if (response.status !== 'success') {
                $('#contestArtworksContainer').html(`
                    <div class="alert alert-danger">
                        ${response.message || 'Ошибка загрузки данных'}
                    </div>
                `).show();
                return;
            }
            
            const container = $('#contestArtworksContainer').empty();
            
            if (response.data.auto_winner) {
                const artwork = response.data.artworks[0];
                container.html(`
                    <div class="col-12 text-center">
                        <div class="alert alert-success">
                            <h4>Автоматически определен победитель!</h4>
                            <p>Работа "${artwork.title}" набрала ${artwork.votes_count} голосов.</p>
                        </div>
                        <div class="contest-artwork winner mb-4">
                            <img src="/uploads/artworks/${artwork.image_path}" 
                                 alt="${artwork.title}" 
                                 class="img-fluid rounded">
                            <div class="artwork-info mt-3">
                                <h3>${artwork.title}</h3>
                                <p>Автор: @${artwork.author}</p>
                                <p>Голосов: ${artwork.votes_count}</p>
                            </div>
                        </div>
                        <button class="btn btn-success confirm-winner-btn" 
                                data-artwork-id="${artwork.id}" 
                                data-contest-id="${contestId}">
                            <i class="fas fa-check"></i> Подтвердить победителя
                        </button>
                    </div>
                `);
            } else {
                container.html(`
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <h4>Несколько работ с одинаковым количеством голосов!</h4>
                            <p>Пожалуйста, выберите победителя вручную.</p>
                        </div>
                        <div class="row">
                            ${response.data.artworks.map(artwork => `
                                <div class="col-md-6 mb-4">
                                    <div class="contest-artwork candidate">
                                        <img src="/uploads/artworks/${artwork.image_path}" 
                                             alt="${artwork.title}" 
                                             class="img-fluid rounded">
                                        <div class="artwork-info mt-3">
                                            <h3>${artwork.title}</h3>
                                            <p>Автор: @${artwork.author}</p>
                                            <p>Голосов: ${artwork.votes_count}</p>
                                        </div>
                                        <button class="btn btn-primary mt-2 confirm-winner-btn" 
                                                data-artwork-id="${artwork.id}" 
                                                data-contest-id="${contestId}">
                                            <i class="fas fa-crown"></i> Выбрать победителем
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `);
            }
            container.show();
        }, 'json').fail(function(error) {
            $('#winnerSelectionProgress').hide();
            $('#contestArtworksContainer').html(`
                <div class="alert alert-danger">
                    Ошибка загрузки данных: ${error.statusText}
                </div>
            `).show();
        }).always(() => btn.prop('disabled', false).html('<i class="fas fa-crown"></i>'));
    });

    // 5. Обработчик подтверждения выбора победителя (единственный!)
    $(document).on('click', '.confirm-winner-btn', function() {
        const artworkId = $(this).data('artwork-id');
        const contestId = $(this).data('contest-id');
        const btn = $(this);
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '../admin/php/set_winner.php',
            type: 'POST',
            data: {
                contest_id: contestId,
                artwork_id: artworkId
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    $('#selectWinnerModal').modal('hide');
                    $('.admin-header-right').prepend(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Победитель конкурса успешно выбран!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Ошибка: ' + (response.message || 'Неизвестная ошибка'));
                }
            },
            error: function(xhr) {
                alert('Ошибка сервера: ' + xhr.statusText + '. Проверьте консоль для подробностей.');
                console.error('Ошибка AJAX:', xhr.responseText);
            },
            complete: function() {
                btn.prop('disabled', false).html('Подтвердить');
            }
        });
    });
    // 6. Обработчик удаления конкурса
$(document).on('click', '.delete-contest-btn', function() {
    const contestId = $(this).data('contest-id');
    
    if (!confirm('Вы уверены, что хотите удалить этот конкурс? Работы останутся в галерее, но будут отвязаны от конкурса.')) {
        return;
    }

    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
        url: '/admin/php/delete_contest.php', // Убедитесь, что путь правильный!
        type: 'POST',
        data: { id: contestId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                // Показываем уведомление
                $('.admin-header-right').prepend(
                    '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                    'Конкурс успешно удален!' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>'
                );
                
                // Обновляем таблицу через 1 секунду
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('Ошибка: ' + (response.message || 'Не удалось удалить конкурс'));
            }
        },
        error: function(xhr) {
            alert('Ошибка сервера: ' + xhr.statusText);
            console.error('Ошибка удаления:', xhr.responseText);
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
        }
    });
});
});