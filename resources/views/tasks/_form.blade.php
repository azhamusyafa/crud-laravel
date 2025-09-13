<form id="taskForm" novalidate>
    @csrf
    <input type="hidden" id="taskId" name="id">
    <input type="hidden" id="formMethod" name="_method" value="POST">
    
    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label for="title" class="form-label">
                    Judul Tugas <span class="text-danger">*</span>
                    <i class="bi bi-question-circle text-muted ms-1" 
                       data-bs-toggle="tooltip" 
                       data-bs-placement="top" 
                       title="Masukkan judul yang jelas dan deskriptif untuk tugas Anda. Minimal 3 karakter."></i>
                </label>
                <input type="text" 
                       class="form-control" 
                       id="title" 
                       name="title" 
                       placeholder="Masukkan judul tugas..."
                       required
                       minlength="3"
                       maxlength="255"
                       pattern="^.{3,255}$"
                       data-validation="title"
                       autocomplete="off"
                       title="Judul tugas minimal 3 karakter, maksimal 255 karakter">
                <div class="invalid-feedback" id="title-error"></div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="status" class="form-label">
                    Status <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="taskStatus" name="status" required>
                    <option value="">Pilih Status</option>
                    @foreach(\App\Models\Task::STATUSES as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" id="status-error"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="due_at" class="form-label">
                    Batas Waktu <small class="text-muted">(opsional)</small>
                    <i class="bi bi-question-circle text-muted ms-1" 
                       data-bs-toggle="tooltip" 
                       data-bs-placement="top" 
                       title="Tentukan kapan tugas ini harus selesai. Akan ada notifikasi jika mendekati batas waktu."></i>
                </label>
                <input type="datetime-local" 
                       class="form-control" 
                       id="due_at" 
                       name="due_at"
                       min="{{ date('Y-m-d\TH:i') }}"
                       data-validation="due_at"
                       title="Pilih tanggal dan waktu untuk batas waktu tugas">
                <div class="form-text">Kosongkan jika tidak ada batas waktu</div>
                <div class="invalid-feedback" id="due_at-error"></div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label for="description" class="form-label">
                    Deskripsi <small class="text-muted">(opsional)</small>
                </label>
                <textarea class="form-control" 
                          id="description" 
                          name="description" 
                          rows="4" 
                          placeholder="Masukkan deskripsi tugas..."
                          maxlength="2000"
                          data-validation="description"
                          title="Deskripsi tugas maksimal 2000 karakter"></textarea>
                <div class="form-text">
                    <span id="descriptionCount">0</span>/2000 karakter
                </div>
                <div class="invalid-feedback" id="description-error"></div>
            </div>
        </div>
    </div>
</form>

<script>
    // Character counter untuk description - handled by tasks.js
    document.addEventListener('DOMContentLoaded', function() {
        const descriptionTextarea = document.getElementById('description');
        
        if (descriptionTextarea) {
            descriptionTextarea.addEventListener('input', function() {
                updateCharacterCounter();
            });
        }
    });
</script>