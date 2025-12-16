document.addEventListener('DOMContentLoaded', function () {
    // 1. URL Params Check
    const params = new URLSearchParams(window.location.search);

    if (params.get("added") === "1") {
        showToast("Pengguna berhasil ditambahkan!", 'success');
        params.delete("added");
        window.history.replaceState({}, "", window.location.pathname);
    }

    if (params.get("added") === "0") {
        showToast("Gagal menambahkan pengguna!", 'error');
        params.delete("added");
        window.history.replaceState({}, "", window.location.pathname);
    }

    // 2. Logout Logic
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", async function (e) {
            e.preventDefault();
            const confirmed = await showConfirm("Yakin mau keluar?");
            if (confirmed) {
                localStorage.removeItem("adminData");
                window.location.href = "../proses/proses_logout.php";
            }
        });
    }

    const logoutBtnMobile = document.getElementById("logoutBtnMobile");
    if (logoutBtnMobile) {
        logoutBtnMobile.addEventListener("click", async function (e) {
            e.preventDefault();
            const confirmed = await showConfirm("Yakin mau keluar?");
            if (confirmed) {
                localStorage.removeItem("adminData");
                window.location.href = "../proses/proses_logout.php";
            }
        });
    }

    // 3. Email Suggestion Logic
    const namaInput = document.getElementById('nama');
    const emailInput = document.getElementById('email');
    const suggestionContainer = document.getElementById('emailSuggestionContainer');

    if (namaInput && emailInput && suggestionContainer) {
        namaInput.addEventListener('input', function () {
            const name = this.value;
            // Basic sanitization: lowercase, remove special chars, replace spaces with nothing
            const cleanName = name.toLowerCase().replace(/[^a-z0-9]/g, '');

            if (cleanName.length > 0) {
                const candidateEmail = cleanName + '@gmail.com';
                // Show clickable badge
                suggestionContainer.style.display = 'block';
                suggestionContainer.innerHTML = `
                    <small class="text-muted d-block mb-1">Rekomendasi:</small>
                    <span class="badge bg-success-subtle text-success border border-success cur-pointer" 
                          style="cursor: pointer; font-size: 0.9rem;"
                          onclick="fillEmail('${candidateEmail}')">
                        <i class="bi bi-magic me-1"></i> ${candidateEmail}
                    </span>
                `;
            } else {
                suggestionContainer.style.display = 'none';
                suggestionContainer.innerHTML = '';
            }
        });

        // Function to handle the click (exposed globally for onclick)
        window.fillEmail = function (email) {
            emailInput.value = email;
            // Optional: visual feedback
            emailInput.classList.add('is-valid');
            setTimeout(() => emailInput.classList.remove('is-valid'), 1000);
        };
    }

    // 4. Session Message (if any)
    if (window.sessionSuccessMessage) {
        showToast(window.sessionSuccessMessage, 'success');
    }
});
