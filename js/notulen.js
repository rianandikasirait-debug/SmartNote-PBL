document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('lampiranContainer');
    const addBtn = document.getElementById('addLampiranBtn');

    if (container && addBtn) {
        function addRow() {
            const row = document.createElement('div');
            row.className = 'card mb-2 p-3 border-light bg-light shadow-sm lampiran-row';
            row.innerHTML = `
                <div class="row align-items-center g-2">
                    <div class="col-md-5">
                        <input type="text" name="judul_lampiran[]" class="form-control form-control-sm" placeholder="Judul Lampiran" required>
                    </div>
                    <div class="col-md-5">
                        <input type="file" name="file_lampiran[]" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-sm btn-soft-danger remove-lampiran">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(row);

            // Handle remove
            row.querySelector('.remove-lampiran').addEventListener('click', function () {
                row.remove();
            });
        }

        addBtn.addEventListener('click', addRow);
    }

    /* =======================
       TINYMCE INITIALIZATION
    ======================= */
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#isi',
            height: 350,
            menubar: false,
            api_key: 'mnqdvqiep8rrq6ozk4hrfn9d8734oxaqe4cyps522sfrd8y3',
            plugins: "lists link table code",
            toolbar: "undo redo | bold italic underline | bullist numlist | link",
        });
    }

    /* =======================
       FORM SUBMISSION
    ======================= */
    const notulenForm = document.getElementById("notulenForm");
    if (notulenForm) {
        notulenForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            try {
                // Sinkronisasi konten TinyMCE ke textarea
                if (typeof tinymce !== 'undefined' && tinymce.get("isi")) {
                    tinymce.triggerSave();
                }

                const fd = new FormData(this);

                // Ambil data peserta yang dipilih dari tabel visual
                document.querySelectorAll('.added-item').forEach(item => {
                    fd.append("peserta[]", item.dataset.id);
                });

                const res = await fetch("../proses/proses_simpan_notulen.php", {
                    method: "POST",
                    body: fd
                });

                const text = await res.text();
                let json;
                try {
                    json = JSON.parse(text);
                } catch (err) {
                    console.error("Invalid JSON:", text);
                    alert("Terjadi kesalahan server: " + text.substring(0, 100));
                    return;
                }

                if (json.success) {
                    showToast('Notulen berhasil disimpan!', 'success');

                    const submitBtn = document.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.disabled = true;

                    setTimeout(() => {
                        location.href = 'dashboard_admin.php';
                    }, 1000);
                } else {
                    showToast(json.message || "Gagal menyimpan data.", 'error');
                }
            } catch (error) {
                console.error(error);
                showToast("Terjadi kesalahan: " + error.message, 'error');
            }
        });
    }

    /* =======================
       PENGELOLAAN PESERTA (MODAL)
    ======================= */
    const selectAll = document.getElementById('selectAll');
    const btnSimpanPeserta = document.getElementById('btnSimpanPeserta');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const addedContainer = document.getElementById('addedContainer');
    const notulenCheckboxes = document.querySelectorAll('.notulen-checkbox');
    const searchInput = document.getElementById('searchInput');
    const notulenItems = document.querySelectorAll('.notulen-item');
    const noResults = document.getElementById('noResults');
    const modalPeserta = document.getElementById('modalPeserta');

    // Search Functionality
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            let hasVisible = false;

            notulenItems.forEach(item => {
                const label = item.querySelector('label').innerText.toLowerCase();
                if (label.includes(filter)) {
                    item.classList.remove('d-none');
                    hasVisible = true;
                } else {
                    item.classList.add('d-none');
                }
            });

            if (noResults) {
                noResults.classList.toggle('d-none', hasVisible);
            }
        });
    }

    // Checkbox Pilih Semua
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            const visibleCheckboxes = Array.from(notulenCheckboxes).filter(cb => !cb.closest('.notulen-item').classList.contains('d-none'));
            visibleCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }

    // Tombol Bersihkan/Reset
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function () {
            notulenCheckboxes.forEach(cb => cb.checked = false);
            if (selectAll) selectAll.checked = false;
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('keyup'));
            }
        });
    }

    // Tombol Simpan Pilihan
    if (btnSimpanPeserta && addedContainer) {
        btnSimpanPeserta.addEventListener('click', function () {
            const selected = document.querySelectorAll('.notulen-checkbox:checked');

            addedContainer.innerHTML = ''; // Clear existing

            if (selected.length === 0) {
                addedContainer.innerHTML = '<tr id="emptyRow" style="border-bottom: 1px solid #dee2e6 !important;"><td colspan="3" class="text-center text-muted py-3">Belum ada peserta yang ditambahkan</td></tr>';
            } else {
                selected.forEach((cb, index) => {
                    const id = cb.value;
                    const name = cb.dataset.name;

                    const tr = document.createElement('tr');
                    tr.className = 'added-item align-middle border-bottom';
                    tr.dataset.id = id;

                    tr.innerHTML = `
                        <td class="px-2 px-md-4 text-center text-muted small">${index + 1}</td>
                        <td class="px-2 px-md-4 text-start">
                            ${escapeHtml(name)}
                        </td>
                        <td class="text-center px-2 px-md-4">
                            <button type="button" class="btn btn-sm btn-danger remove-btn text-white" data-id="${id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;
                    addedContainer.appendChild(tr);
                });
            }

            const modalInstance = bootstrap.Modal.getInstance(modalPeserta);
            if (modalInstance) {
                modalInstance.hide();
            }
            showToast('Daftar peserta diperbarui', 'success');
        });
    }

    // Event delegation for remove buttons
    if (addedContainer) {
        addedContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-btn') || e.target.closest('.remove-btn')) {
                e.preventDefault();
                const btn = e.target.classList.contains('remove-btn') ? e.target : e.target.closest('.remove-btn');
                const id = btn.dataset.id;

                // Uncheck di modal
                const modalCb = document.querySelector(`.notulen-checkbox[value="${id}"]`);
                if (modalCb) modalCb.checked = false;

                const item = btn.closest('.added-item');
                if (item) item.remove();

                // Re-numbering
                const remainingItems = addedContainer.querySelectorAll('.added-item');
                remainingItems.forEach((row, index) => {
                    row.querySelector('td').innerText = index + 1;
                });

                if (remainingItems.length === 0) {
                    addedContainer.innerHTML = '<tr id="emptyRow" style="border-bottom: 1px solid #dee2e6 !important;"><td colspan="3" class="text-center text-muted py-3">Belum ada peserta yang ditambahkan</td></tr>';
                }
            }
        });
    }

    // Logout Handlers
    const logoutHandlers = [document.getElementById("logoutBtn"), document.getElementById("logoutBtnMobile")];
    logoutHandlers.forEach(btn => {
        if (btn) {
            btn.addEventListener("click", async function (e) {
                e.preventDefault();
                const confirmed = await showConfirm("Yakin mau keluar?");
                if (confirmed) {
                    localStorage.removeItem("adminData");
                    window.location.href = "../proses/proses_logout.php";
                }
            });
        }
    });

});

// Helper Functions (Global or Scoped?)
// escapeHtml is used inside addRow, so it needs to be accessible.
function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function showToast(message, type = 'success') {
    const toastEl = document.getElementById('successToast');
    if (!toastEl) return;

    toastEl.className = `toast align-items-center text-bg-${type} border-0`;
    toastEl.querySelector('.toast-body').innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> ${message}`;
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
}
