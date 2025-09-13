$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    initializeEventListeners();
    
    console.log('Tasks.js loaded successfully');
});

function initializeEventListeners() {
    $('#taskModal').on('show.bs.modal', function(e) {
        resetForm();
        clearErrors();
    });
    
    $('#btnSaveTask').on('click', function() {
        submitTaskForm();
    });
    
    $('#taskForm').on('submit', function(e) {
        e.preventDefault();
        submitTaskForm();
    });
    
    $('#btnCreate, #btnCreateEmpty').on('click', function() {
        prepareCreateForm();
    });
    
    $('#title').on('input', function() {
        validateField('title', $(this).val());
    });
    
    $('#taskStatus').on('change', function() {
        validateField('status', $(this).val());
    });
    
    $('#due_at').on('change', function() {
        validateField('due_at', $(this).val());
    });
}

function prepareCreateForm() {
    $('#taskModalLabel').text('Tambah Tugas');
    $('#formMethod').val('POST');
    $('#taskId').val('');
    $('#saveText').text('Simpan');
    
    $('#taskStatus').val('To-Do');
    
    resetForm();
    clearErrors();
}

function editTask(taskId) {
    $('#taskModalLabel').text('Edit Tugas');
    $('#formMethod').val('PUT');
    $('#taskId').val(taskId);
    $('#saveText').text('Update');
    
    if (window.ClientValidation && typeof ClientValidation.validateForm === 'function') {
        if (!ClientValidation.validateForm()) {
            showToast('Periksa kembali data yang diinput', 'error');
            return;
        }
    }
    
    showLoadingState(true);
    clearErrors();
    
    $.ajax({
        url: `/tasks/${taskId}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                populateForm(response.data);
                $('#taskModal').modal('show');
            } else {
                showToast('Gagal memuat data tugas', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error fetching task:', xhr);
            showToast('Gagal memuat data tugas', 'error');
        },
        complete: function() {
            showLoadingState(false);
        }
    });
}

function populateForm(task) {
    $('#title').val(task.title);
    $('#description').val(task.description || '');
    $('#taskStatus').val(task.status);
    $('#due_at').val(task.due_at || '');
    
    updateCharacterCounter();
}

function submitTaskForm() {
    const taskId = $('#taskId').val();
    const method = $('#formMethod').val();
    const isUpdate = method === 'PUT';
    
    showLoadingState(true);
    clearErrors();
    
    const formData = {
        title: $('#title').val().trim(),
        description: $('#description').val().trim(),
        status: $('#taskStatus').val(),
        due_at: $('#due_at').val() || null
    };
    
    if (isUpdate) {
        formData._method = 'PUT';
    }
    
    const url = isUpdate ? `/tasks/${taskId}` : '/tasks';
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#taskModal').modal('hide');
                
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tasksTable')) {
                    $(document).trigger(isUpdate ? 'taskUpdated' : 'taskCreated', [response.data]);
                    setTimeout(() => {
                        window.location.reload(); 
                    }, 1000);
                } else {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } else {
                showToast(response.message || 'Terjadi kesalahan', 'error');
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                displayValidationErrors(errors);
                showToast('Periksa data yang diinput', 'error');
            } else {
                console.error('Error submitting form:', xhr);
                showToast('Terjadi kesalahan server', 'error');
            }
        },
        complete: function() {
            showLoadingState(false);
        }
    });
}

function deleteTask(taskId, taskTitle) {
    if (confirm(`Apakah Anda yakin ingin menghapus tugas "${taskTitle}"?`)) {
        // Show loading on the delete button
        const $deleteBtn = $(`.btn-outline-danger[onclick*="${taskId}"]`);
        const originalHtml = $deleteBtn.html();
        $deleteBtn.html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);
        
        $.ajax({
            url: `/tasks/${taskId}`,
            type: 'POST',
            data: {
                _method: 'DELETE'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    
                    // Handle DataTables case
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tasksTable')) {
                        const table = $('#tasksTable').DataTable();
                        table.row(`[data-task-id="${taskId}"]`).remove().draw();
                        
                        // Update page info after deletion
                        setTimeout(() => {
                            updateTaskStatistics();
                        }, 300);
                    } else {
                        // Handle regular table case
                        const $row = $(`tr[data-task-id="${taskId}"]`);
                        
                        // Animate row removal
                        $row.addClass('table-danger');
                        setTimeout(() => {
                            $row.fadeOut(400, function() {
                                $(this).remove();
                                
                                // Update row numbers
                                updateRowNumbers();
                                
                                // Update statistics
                                updateTaskStatistics();
                                
                                // Check if no more rows and show empty state
                                checkEmptyState();
                            });
                        }, 200);
                    }
                } else {
                    showToast(response.message || 'Gagal menghapus tugas', 'error');
                    $deleteBtn.html(originalHtml).prop('disabled', false);
                }
            },
            error: function(xhr) {
                console.error('Error deleting task:', xhr);
                showToast('Terjadi kesalahan server', 'error');
                $deleteBtn.html(originalHtml).prop('disabled', false);
            }
        });
    }
}

// Update row numbers after deletion
function updateRowNumbers() {
    $('#tasksTable tbody tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
    });
}

// Update task statistics after CRUD operations
function updateTaskStatistics() {
    $.ajax({
        url: '/tasks',
        type: 'GET',
        data: { ajax_stats: true },
        dataType: 'json',
        success: function(response) {
            if (response.stats) {
                // Update statistics cards
                $('.card .card-body:contains("Total") h3').text(response.stats.total);
                $('.card .card-body:contains("To-Do") h3').text(response.stats.todo);
                $('.card .card-body:contains("In Progress") h3').text(response.stats.in_progress);
                $('.card .card-body:contains("Done") h3').text(response.stats.done);
            }
        },
        error: function() {
            console.warn('Failed to update statistics');
        }
    });
}

// Check and show empty state
function checkEmptyState() {
    const remainingRows = $('#tasksTable tbody tr:visible').length;
    
    if (remainingRows === 0) {
        // Hide table and pagination
        $('.table-responsive').hide();
        $('.pagination').parent().hide();
        
        // Show empty state
        const emptyStateHtml = `
            <div class="text-center py-5" id="emptyState">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">Semua tugas telah dihapus</h4>
                <p class="text-muted">Silakan tambah tugas baru dengan mengklik tombol "Tambah Tugas"</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal" onclick="prepareCreateForm()">
                    <i class="bi bi-plus-lg"></i> Tambah Tugas Baru
                </button>
            </div>
        `;
        
        $('.card .card-body').append(emptyStateHtml);
    }
}

function displayValidationErrors(errors) {
    clearErrors();
    
    Object.keys(errors).forEach(field => {
        const $field = $(`#${field}`);
        const $errorContainer = $(`#${field}-error`);
        
        if ($field.length && $errorContainer.length) {
            $field.addClass('is-invalid');
            $errorContainer.text(errors[field][0]).show();
        }
    });
    
    if (Object.keys(errors).length > 0) {
        const errorList = Object.values(errors).flat();
        const errorHtml = errorList.map(error => `<li>${error}</li>`).join('');
        
        $('#taskErrors').html(errorHtml);
        $('#taskAlert').removeClass('d-none');
    }
}

function clearErrors() {
    $('.is-invalid').removeClass('is-invalid');
    
    $('.invalid-feedback').text('').hide();
    
    $('#taskAlert').addClass('d-none');
    $('#taskErrors').html('');
}

function validateField(fieldName, value) {
    $(`#${fieldName}`).removeClass('is-invalid');
    $(`#${fieldName}-error`).text('').hide();
    
    let isValid = true;
    let errorMessage = '';
    
    switch (fieldName) {
        case 'title':
            if (!value || value.trim().length === 0) {
                isValid = false;
                errorMessage = 'Judul tugas wajib diisi';
            } else if (value.length < 3) {
                isValid = false;
                errorMessage = 'Judul tugas minimal 3 karakter';
            } else if (value.length > 255) {
                isValid = false;
                errorMessage = 'Judul tugas maksimal 255 karakter';
            }
            break;
            
        case 'status':
            const validStatuses = ['To-Do', 'In Progress', 'Done'];
            if (!value || !validStatuses.includes(value)) {
                isValid = false;
                errorMessage = 'Status wajib dipilih';
            }
            break;
            
        case 'due_at':
            if (value) {
                const dueDate = new Date(value);
                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                
                if (isNaN(dueDate.getTime())) {
                    isValid = false;
                    errorMessage = 'Format tanggal tidak valid';
                } else if (dueDate < today && $('#formMethod').val() === 'POST') {
                    isValid = false;
                    errorMessage = 'Batas waktu tidak boleh tanggal lampau';
                }
            }
            break;
    }
    
    if (!isValid) {
        $(`#${fieldName}`).addClass('is-invalid');
        $(`#${fieldName}-error`).text(errorMessage).show();
    }
    
    return isValid;
}

function resetForm() {
    $('#taskForm')[0].reset();
    $('#taskId').val('');
    $('#formMethod').val('POST');
    $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
    $('.invalid-feedback, .valid-feedback').hide().text('');
    $('.validation-icon').remove();
    $('.field-warning').remove();
    $('.character-progress').remove();
    
    updateCharacterCounter();
    
    $('#due_at').attr('min', new Date().toISOString().slice(0, 16));
}

function showLoadingState(show) {
    if (show) {
        $('#saveSpinner').removeClass('d-none');
        $('#btnSaveTask').prop('disabled', true);
    } else {
        $('#saveSpinner').addClass('d-none');
        $('#btnSaveTask').prop('disabled', false);
    }
}

function updateCharacterCounter() {
    const description = $('#description').val() || '';
    const length = description.length;
    $('#descriptionCount').text(length);
    
    if (length > 1800) {
        $('#descriptionCount').removeClass('text-muted text-warning').addClass('text-warning');
    } else if (length > 2000) {
        $('#descriptionCount').removeClass('text-muted text-warning').addClass('text-danger');
    } else {
        $('#descriptionCount').removeClass('text-warning text-danger').addClass('text-muted');
    }
}

function showToast(message, type = 'success') {
    const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    const icon = type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill';
    
    const toastHtml = `
        <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${icon} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    if ($('.toast-container').length === 0) {
        $('body').append('<div class="toast-container"></div>');
    }
    
    const $toast = $(toastHtml);
    $('.toast-container').append($toast);
    
    const toast = new bootstrap.Toast($toast[0]);
    toast.show();
    
    $toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

function formatDate(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function refreshTableRow(taskData) {
    const $row = $(`tr[data-task-id="${taskData.id}"]`);
    if ($row.length) {
        const statusBadge = `<span class="badge ${taskData.status_badge_class} status-badge">${taskData.status}</span>`;
        let dueDateHtml = '<span class="text-muted">-</span>';
        
        if (taskData.due_at_formatted) {
            const statusClass = taskData.is_overdue ? 'text-danger' : (taskData.is_upcoming ? 'text-warning' : '');
            dueDateHtml = `<span class="${statusClass}">${taskData.due_at_formatted}</span>`;
        }
        
        $row.find('td:nth-child(2) .fw-bold').text(taskData.title);
        $row.find('td:nth-child(3)').html(statusBadge);
        $row.find('td:nth-child(4)').html(dueDateHtml);
        
        $row.addClass('table-success');
        setTimeout(() => {
            $row.removeClass('table-success');
        }, 2000);
    }
}