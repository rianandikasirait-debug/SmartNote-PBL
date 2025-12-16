document.addEventListener('DOMContentLoaded', function () {
    // Session success message
    if (window.sessionSuccessMessage) {
        showToast(window.sessionSuccessMessage, 'success');
    }

    // Logout Logic
    async function confirmLogout(e) {
        e.preventDefault();
        const confirmed = await showConfirm("Yakin mau keluar?");
        if (confirmed) window.location.href = "../proses/proses_logout.php";
    }

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) logoutBtn.addEventListener("click", confirmLogout);

    const logoutBtnMobile = document.getElementById("logoutBtnMobile");
    if (logoutBtnMobile) logoutBtnMobile.addEventListener("click", confirmLogout);
});

function showToast(message, type = 'success') {

}
