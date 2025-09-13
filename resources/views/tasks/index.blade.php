@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Daftar Tugas</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal" id="btnCreate">
                <i class="bi bi-plus-lg"></i> Tambah Tugas
            </button>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary">Total</h5>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-secondary">
                    <div class="card-body text-center">
                        <h5 class="card-title text-secondary">To-Do</h5>
                        <h3 class="mb-0">{{ $stats['todo'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h5 class="card-title text-warning">In Progress</h5>
                        <h3 class="mb-0">{{ $stats['in_progress'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success">Done</h5>
                        <h3 class="mb-0">{{ $stats['done'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Filter & Pencarian</h6>
            </div>
            <div class="card-body">
                <form method="GET" id="filterForm" class="row g-3">
                    <div class="col-md-6">
                        <label for="advancedSearch" class="form-label">
                            <i class="bi bi-search"></i> Pencarian
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="advancedSearch" 
                               name="search" 
                               placeholder="Cari judul atau deskripsi tugas..."
                               value="{{ request('search') }}">
                        <div class="form-text">
                            <small class="text-muted">
                                <span id="searchMode">
                                    @if($tasks->total() > 25)
                                        DataTables: Pencarian real-time aktif
                                    @else
                                        Mode dasar: Tekan Enter untuk mencari
                                    @endif
                                </span>
                            </small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Filter Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption }}" {{ request('status') == $statusOption ? 'selected' : '' }}>
                                    {{ $statusOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="sort" class="form-label">Urutkan</label>
                        <select name="sort" id="sort" class="form-select">
                            <option value="">Tanggal Dibuat</option>
                            <option value="due_at" {{ request('sort') == 'due_at' ? 'selected' : '' }}>
                                Batas Waktu
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="order" class="form-label">Urutan</label>
                        <select name="order" id="order" class="form-select">
                            <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>Naik</option>
                            <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>Turun</option>
                        </select>
                    </div>
                </form>
                
                @if($tasks->total() > 100)
                    <div class="mt-3 alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Dataset Besar Terdeteksi:</strong> 
                        Dengan {{ $tasks->total() }} tugas, kami menggunakan DataTables untuk performa optimal.
                        <a href="#serverSideNote" class="alert-link">Pelajari tentang server-side processing</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @if($tasks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="tasksTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">Judul</th>
                                    <th width="20%">Status</th>
                                    <th width="20%">Batas Waktu</th>
                                    <th width="15%">Dibuat</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tasks as $task)
                                    <tr data-task-id="{{ $task->id }}">
                                        <td>{{ $loop->iteration + ($tasks->currentPage() - 1) * $tasks->perPage() }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $task->title }}</div>
                                            @if($task->description)
                                                <small class="text-muted">{{ Str::limit($task->description, 100) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $task->status_badge_class }} status-badge">
                                                {{ $task->status }}
                                            </span>
                                            @if($task->is_overdue)
                                                <br><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Terlambat</small>
                                            @elseif($task->is_upcoming)
                                                <br><small class="text-warning"><i class="bi bi-clock"></i> Segera</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->due_at)
                                                <span class="{{ $task->is_overdue ? 'text-danger' : ($task->is_upcoming ? 'text-warning' : '') }}">
                                                    {{ $task->due_at_formatted }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $task->created_at->format('d M Y H:i') }}</small>
                                        </td>
                                        <td class="text-center table-actions">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                                    onclick="editTask({{ $task->id }})" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteTask({{ $task->id }}, '{{ addslashes($task->title) }}')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2 small text-muted">
                        Debug: Total={{ $tasks->total() }}, PerPage={{ $tasks->perPage() }}, 
                        CurrentPage={{ $tasks->currentPage() }}, LastPage={{ $tasks->lastPage() }}
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pagination-wrapper">
                        <div class="text-muted">
                            Menampilkan {{ $tasks->firstItem() }} - {{ $tasks->lastItem() }} dari {{ $tasks->total() }} tugas
                        </div>
                        <div>
                            @if($tasks->lastPage() > 1)
                                {{ $tasks->links('custom-pagination') }}
                            @else
                                <span class="text-muted">Hanya 1 halaman</span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="mt-3 text-muted">Belum ada tugas</h4>
                        <p class="text-muted">Silakan tambah tugas baru dengan mengklik tombol "Tambah Tugas"</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal" id="btnCreateEmpty">
                            <i class="bi bi-plus-lg"></i> Tambah Tugas Pertama
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('tasks._modal')

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const useDataTables = {{ $tasks->total() > 50 ? 'true' : 'false' }}; 
        const totalTasks = {{ $tasks->total() }};
        const currentCount = {{ $tasks->count() }};
        
        console.log('Total tasks:', totalTasks);
        console.log('Current page tasks:', currentCount);
        console.log('Use DataTables:', useDataTables);
        const hasData = {{ $tasks->count() > 0 ? 'true' : 'false' }};
        
        if (hasData) {
            if (useDataTables) {
                initializeDataTables();
            } else {
                initializeBasicFilters();
            }
        }
        
        initializeTaskActions();
    });
    
    function initializeDataTables() {
        if ($.fn.DataTable) {
            const table = $('#tasksTable').DataTable({
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "order": [[4, "desc"]], 
                "columnDefs": [
                    {
                        "targets": [0, 5], 
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "targets": [2], /
                        "type": "string",
                        "render": function(data, type, row) {
                            if (type === 'display') {
                                return data; 
                            }
                            return $(data).text();
                        }
                    },
                    {
                        "targets": [3], 
                        "type": "date",
                        "render": function(data, type, row) {
                            if (type === 'display') {
                                return data;
                            }
                            const dateText = $(data).text();
                            return dateText === '-' ? '' : dateText;
                        }
                    }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json",
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ tugas per halaman",
                    "info": "Menampilkan _START_ - _END_ dari _TOTAL_ tugas",
                    "infoEmpty": "Tidak ada tugas",
                    "infoFiltered": "(difilter dari _MAX_ total tugas)",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir", 
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    },
                    "emptyTable": "Tidak ada tugas yang tersedia"
                },
                "responsive": true,
                "autoWidth": false,
                "processing": true,
                "deferRender": true
            });
            
            setupDataTablesFilters(table);
            
            $('body').addClass('datatables-active');
            $('.pagination-wrapper').hide();
            
            $('.dataTables_wrapper').addClass('mt-3');
            
            console.log('DataTables initialized for large dataset');
        }
    }
    
    function initializeBasicFilters() {
        const filterSelects = ['#status', '#sort', '#order'];
        
        filterSelects.forEach(selector => {
            $(selector).on('change', function() {
                $('#filterForm').submit();
            });
        });
        
        $('body').removeClass('datatables-active');
        $('.pagination-wrapper').show();
        
        console.log('Basic filters initialized for small dataset');
    }

    function setupDataTablesFilters(table) {
   
        $('#status').on('change', function() {
            const status = $(this).val();
            if (status === '') {
                table.column(2).search('').draw();
            } else {
                table.column(2).search(status).draw();
            }
        });
        
        $('#sort, #order').on('change', function() {
            const sort = $('#sort').val();
            const order = $('#order').val();
            
            if (sort === 'due_at') {
                const orderIndex = order === 'desc' ? 'desc' : 'asc';
                table.order([3, orderIndex]).draw();
            } else {
                table.order([4, 'desc']).draw();
            }
        });
        
        $('#advancedSearch').on('keyup change', function() {
            table.search($(this).val()).draw();
        });
    }
    
    function initializeBasicFilters() {
        const filterSelects = ['#status', '#sort', '#order'];
        
        filterSelects.forEach(selector => {
            $(selector).on('change', function() {
                $('#filterForm').submit();
            });
        });
        
        console.log('Basic filters initialized for small dataset');
    }
    
    function initializeTaskActions() {
        $(document).on('taskCreated taskUpdated', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tasksTable')) {
                $('#tasksTable').DataTable().ajax.reload();
            } else {
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        });
        
        $(document).on('taskDeleted', function(e, taskId) {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tasksTable')) {
                const table = $('#tasksTable').DataTable();
                table.row(`[data-task-id="${taskId}"]`).remove().draw();
            }
        });
    }
</script>
@endpush