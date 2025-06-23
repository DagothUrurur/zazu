document.addEventListener('DOMContentLoaded', function() {
    // Переключение боковой панели
    const menuToggle = document.querySelector('.admin-menu-toggle');
    const container = document.querySelector('.admin-container');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (menuToggle && container && sidebar) {
        menuToggle.addEventListener('click', function() {
            container.classList.toggle('admin-sidebar-collapsed');
            sidebar.classList.toggle('admin-sidebar-collapsed');
        });
    }
    
    // Модальное окно (если оно есть на странице)
    const modal = document.querySelector('.admin-modal');
    const modalClose = document.querySelector('.admin-modal-close');
    
    if (modal && modalClose) {
        const editButtons = document.querySelectorAll('.admin-edit-btn');
        
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });
        
        modalClose.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        });
        
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
    
    // Анимация кнопок
    const quickButtons = document.querySelectorAll('.admin-quick-btn');
    
    quickButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'scale(1.1)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'scale(1)';
            }
        });
    });
    
    // Эффекты для таблицы
    const tableRows = document.querySelectorAll('.admin-table tbody tr');
    
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            const actions = this.querySelector('.admin-actions');
            if (actions) {
                actions.style.opacity = '1';
            }
        });
        
        row.addEventListener('mouseleave', function() {
            const actions = this.querySelector('.admin-actions');
            if (actions) {
                actions.style.opacity = '0.7';
            }
        });
    });
    
    // Инициализация действий
    const actions = document.querySelectorAll('.admin-action-btn');
    
    actions.forEach(action => {
        action.style.opacity = '0.7';
        
        action.addEventListener('mouseenter', function() {
            this.style.opacity = '1';
            this.style.transform = 'translateY(-2px)';
        });
        
        action.addEventListener('mouseleave', function() {
            this.style.opacity = '0.7';
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Случайное мерцание элементов
    setInterval(function() {
        const elements = document.querySelectorAll('.admin-logo-text, .admin-nav-title');
        elements.forEach(el => {
            if (Math.random() > 0.7) {
                el.style.opacity = '0.7';
                setTimeout(() => {
                    el.style.opacity = '1';
                }, 100);
            }
        });
    }, 3000);
    
    // Эффект для поиска
    const searchInput = document.querySelector('.admin-search input');
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            this.parentElement.querySelector('i').style.color = 'var(--admin-gold)';
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement.querySelector('i').style.color = 'var(--admin-parchment-dark)';
        });
    }
});