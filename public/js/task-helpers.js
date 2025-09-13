
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Network error handler
function handleNetworkError(xhr) {
    let message = 'Terjadi kesalahan jaringan';
    
    if (xhr.status === 0) {
        message = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
    } else if (xhr.status === 404) {
        message = 'Halaman tidak ditemukan';
    } else if (xhr.status === 500) {
        message = 'Terjadi kesalahan server';
    } else if (xhr.status === 422) {
        message = 'Data yang dikirim tidak valid';
    }
    
    return message;
}

// Local storage helpers untuk form data backup
const FormBackup = {
    save: function(formData) {
        try {
            localStorage.setItem('taskFormBackup', JSON.stringify(formData));
        } catch (e) {
            console.warn('Cannot save form backup:', e);
        }
    },
    
    load: function() {
        try {
            const backup = localStorage.getItem('taskFormBackup');
            return backup ? JSON.parse(backup) : null;
        } catch (e) {
            console.warn('Cannot load form backup:', e);
            return null;
        }
    },
    
    clear: function() {
        try {
            localStorage.removeItem('taskFormBackup');
        } catch (e) {
            console.warn('Cannot clear form backup:', e);
        }
    }
};