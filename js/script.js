    // Feather Icons init
    feather.replace();

    // Modal logic
    document.addEventListener("DOMContentLoaded", function () {
    const imageModal = document.getElementById("imageModal");
    imageModal.addEventListener("show.bs.modal", function (event) {
        const triggerElement = event.relatedTarget;
        const imageSrc = triggerElement.getAttribute("data-image-src");
        const imageTitle = triggerElement.getAttribute("data-image-title");

        const modalImage = imageModal.querySelector("#modalImage");
        const modalTitle = imageModal.querySelector(".modal-title");

        modalImage.src = imageSrc;
        modalTitle.textContent = imageTitle;
    });
    });

    // Active link highlight
    const navLinks = document.querySelectorAll(".navbar .nav-link");
    navLinks.forEach((link) => {
    link.addEventListener("click", () => {
        navLinks.forEach((l) => l.classList.remove("active"));
        link.classList.add("active");
    });
    });

    // === AUTO CLOSE NAVBAR SAAT KLIK DI LUAR ATAU LINK DI DALAM ===
    document.addEventListener("click", function (event) {
    const navbarCollapse = document.querySelector(".navbar-collapse");
    const navbarToggler = document.querySelector(".navbar-toggler");

    if (!navbarCollapse || !navbarToggler) return;

    const isNavbarOpen = navbarCollapse.classList.contains("show");
    const clickedInsideNavbar = navbarCollapse.contains(event.target);
    const clickedOnToggler = navbarToggler.contains(event.target);
    const clickedOnLink = event.target.closest(".nav-link");

    // Jika klik di luar navbar saat terbuka
    if (isNavbarOpen && !clickedInsideNavbar && !clickedOnToggler) {
        const collapse = bootstrap.Collapse.getInstance(navbarCollapse);
        collapse.hide();
    }

    // Jika klik link di dalam menu (hanya saat hamburger aktif)
    if (isNavbarOpen && clickedOnLink) {
        const collapse = bootstrap.Collapse.getInstance(navbarCollapse);
        collapse.hide();
    }
    });
