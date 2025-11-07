// register_script.js

const registerform = document.getElementById('registerform');

registerform.addEventListener('submit', function (event) {
    event.preventDefault();

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const role = "peserta"; // Role default untuk registrasi adalah 'peserta'

    if (!name || !email || !password) {
        alert('Mohon isi semua kolom sebelum mendaftar!');
        return;
    }

    // Ambil data user yang sudah ada, atau array kosong jika belum ada
    let users = JSON.parse(localStorage.getItem("userData") || "[]");

    // Cek apakah email sudah terdaftar
    const emailExists = users.find(user => user.email === email);
    if (emailExists) {
        alert("Email ini sudah terdaftar. Silakan gunakan email lain atau login.");
        return;
    }

    // Buat objek user baru
    const newUser = {
        name,
        email,
        password,
        role
    };
    
    // Tambahkan user baru ke array
    users.push(newUser);

    // Simpan array users yang diperbarui kembali ke Local Storage
    localStorage.setItem('userData', JSON.stringify(users));

    alert("Registrasi sebagai peserta berhasil! Silakan login.");
    window.location.href = "login.html";
});