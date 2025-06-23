document.addEventListener('DOMContentLoaded', function() {
    // Обработка загрузки изображений галереи
    const galleryUpload = document.getElementById('galleryUpload');
    const galleryUploadPreview = document.getElementById('galleryUploadPreview');
    
    if (galleryUpload) {
        galleryUpload.addEventListener('change', function(e) {
            galleryUploadPreview.innerHTML = '';
            
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const preview = document.createElement('div');
                    preview.className = 'admin-gallery-upload-preview';
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Превью">
                        <span>${file.name}</span>
                    `;
                    galleryUploadPreview.appendChild(preview);
                }
                
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Удаление изображений из галереи
    window.removeGalleryItem = function(btn) {
        if (confirm('Удалить это изображение из галереи?')) {
            const item = btn.closest('.admin-gallery-item');
            item.remove();
        }
    }
    
    // Валидация формы перед отправкой
    const form = document.querySelector('.admin-article-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const description = document.getElementById('description').value.trim();
            
            if (!name || !description) {
                e.preventDefault();
                alert('Пожалуйста, заполните все обязательные поля');
                return false;
            }
            
            return true;
        });
    }
});