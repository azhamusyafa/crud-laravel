<!-- DataTables Information Modal -->
<div class="modal fade" id="serverSideInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tentang DataTables & Server-side Processing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Apa itu DataTables?</h6>
                <p>DataTables adalah plugin jQuery yang memberikan fitur advanced untuk tabel HTML seperti:</p>
                <ul>
                    <li><strong>Pencarian real-time</strong> - Filter data saat Anda mengetik</li>
                    <li><strong>Sorting multi-kolom</strong> - Klik header untuk mengurutkan</li>
                    <li><strong>Pagination dinamis</strong> - Navigasi data yang efisien</li>
                    <li><strong>Responsive design</strong> - Otomatis menyesuaikan layar</li>
                </ul>
                
                <h6 class="mt-4">Server-side Processing</h6>
                <p>Untuk dataset besar (>1000 rows), kami menggunakan server-side processing:</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <strong>Keuntungan:</strong>
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li>Performa tetap cepat</li>
                                    <li>Memory usage rendah</li>
                                    <li>Loading time konsisten</li>
                                    <li>Mendukung jutaan records</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <strong>Cara Kerja:</strong>
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li>Data dimuat per halaman</li>
                                    <li>Filter diproses di server</li>
                                    <li>Sorting dilakukan database</li>
                                    <li>Hanya data yang perlu ditampilkan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-lightbulb"></i>
                    <strong>Tips:</strong> Gunakan kotak pencarian untuk filter data secara real-time. 
                    Klik pada header kolom untuk mengurutkan data.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>