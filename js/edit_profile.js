document.addEventListener("DOMContentLoaded", function () {
    // Preview Image Logic
    const fotoInput = document.getElementById('fotoInput');
    const previewImage = document.getElementById('previewImage');
    const defaultIcon = document.getElementById('defaultIcon');

    if (fotoInput) {
        fotoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (previewImage) {
                        previewImage.src = e.target.result;
                        previewImage.classList.remove('d-none');
                    }
                    if (defaultIcon) defaultIcon.classList.add('d-none');
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Fungsi Hapus Foto dengan Konfirmasi
    window.confirmDeletePhoto = async function () {
        // Ensure showConfirm is available (from admin.js)
        if (typeof showConfirm !== 'function') {
            if (confirm("Hapus foto profil ini?")) {
                submitDeletePhoto();
            }
            return;
        }

        const confirmed = await showConfirm("Hapus foto profil ini?");
        if (confirmed) {
            submitDeletePhoto();
        }
    };

    function submitDeletePhoto() {
        const form = document.querySelector('form');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_photo';
        input.value = '1';
        form.appendChild(input);
        form.submit();
    }

    // Fungsi Logout
    async function confirmLogout(e) {
        e.preventDefault();
        // Ensure showConfirm is available
        if (typeof showConfirm !== 'function') {
            if (confirm("Yakin mau keluar?")) {
                window.location.href = "../proses/proses_logout.php";
            }
            return;
        }

        const confirmed = await showConfirm("Yakin mau keluar?");
        if (confirmed) {
            window.location.href = "../proses/proses_logout.php";
        }
    }

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", confirmLogout);
    }

    const logoutBtnMobile = document.getElementById("logoutBtnMobile");
    if (logoutBtnMobile) {
        logoutBtnMobile.addEventListener("click", confirmLogout);
    }
});
