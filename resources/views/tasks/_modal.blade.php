<!-- Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">Tambah Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="taskAlert" class="alert alert-danger d-none" role="alert">
                    <ul id="taskErrors" class="mb-0"></ul>
                </div>
                
                @include('tasks._form')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveTask">
                    <span class="spinner-border spinner-border-sm d-none" id="saveSpinner" role="status"></span>
                    <span id="saveText">Simpan</span>
                </button>
            </div>
        </div>
    </div>
</div>