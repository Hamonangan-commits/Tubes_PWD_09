// assets/js/main.js
// Fungsi utama: validasi, geolocation, AJAX, preview foto

document.addEventListener('DOMContentLoaded', function () {

    // === 1. Geolocation: Isi lokasi otomatis ===
    window.getLocation = function () {
        const lokasiInput = document.getElementById('lokasi_jemput');
        if (!lokasiInput) return;

        if (navigator.geolocation) {
            lokasiInput.placeholder = "Mengambil lokasi...";
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude.toFixed(6);
                    const lng = position.coords.longitude.toFixed(6);
                    lokasiInput.value = `Lat: ${lat}, Lng: ${lng}`;
                    lokasiInput.placeholder = "Lokasi jemput (otomatis)";
                },
                (error) => {
                    console.error("Geolocation error:", error);
                    lokasiInput.placeholder = "Izinkan lokasi atau isi manual";
                    alert("Gagal mengambil lokasi. Pastikan izin lokasi diaktifkan.");
                }
            );
        } else {
            alert("Browser Anda tidak mendukung geolocation.");
        }
    };

    // === 2. AJAX: Cek duplikat email/username saat registrasi ===
    const emailInput = document.querySelector('input[name="email"]');
    const usernameInput = document.querySelector('input[name="username"]');
    const loadingEl = document.createElement('span');
    loadingEl.className = 'loading ms-2';
    loadingEl.textContent = 'Memeriksa...';

    function checkDuplicate(field, value, type) {
        if (!value) return;

        // Tambahkan loading
        const parent = field.parentElement;
        const existingLoading = parent.querySelector('.loading');
        if (existingLoading) existingLoading.remove();
        parent.appendChild(loadingEl);

        fetch('check_duplicate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `type=${type}&value=${encodeURIComponent(value)}`
        })
        .then(response => response.json())
        .then(data => {
            loadingEl.remove();
            if (data.exists) {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                field.nextElementSibling?.remove(); // hapus pesan lama
                const error = document.createElement('div');
                error.className = 'invalid-feedback';
                error.textContent = type === 'email' ? 'Email sudah digunakan' : 'Username sudah digunakan';
                field.parentElement.appendChild(error);
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                field.nextElementSibling?.remove(); // hapus pesan error lama
            }
        })
        .catch(err => {
            console.error('Error:', err);
            loadingEl.remove();
        });
    }

    if (emailInput) {
        emailInput.addEventListener('blur', () => {
            checkDuplicate(emailInput, emailInput.value, 'email');
        });
    }

    if (usernameInput) {
        usernameInput.addEventListener('blur', () => {
            checkDuplicate(usernameInput, usernameInput.value, 'username');
        });
    }

    // === 3. Preview Foto Profil Saat Upload ===
    const fotoInput = document.querySelector('input[name="foto"]');
    const fotoPreview = document.querySelector('#foto-preview');

    if (fotoInput) {
        fotoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (fotoPreview) {
                        fotoPreview.src = e.target.result;
                        fotoPreview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // === 4. Validasi Tanggal di Form Sewa ===
    const tglMulai = document.querySelector('input[name="tgl_mulai"]');
    const tglSelesai = document.querySelector('input[name="tgl_selesai"]');

    if (tglMulai && tglSelesai) {
        // Set min tanggal hari ini
        const today = new Date().toISOString().split('T')[0];
        tglMulai.min = today;
        tglSelesai.min = today;

        tglMulai.addEventListener('change', () => {
            if (tglMulai.value) {
                tglSelesai.min = tglMulai.value;
            }
        });
    }

});