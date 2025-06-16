const params = new URLSearchParams(window.location.search);
if (params.get('error') === 'wrong_password') {
  alert("Password salah. Silakan coba lagi.");
} else if (params.get('error') === 'email_not_found') {
  alert("Email tidak ditemukan. Silakan periksa kembali atau daftar akun.");
} else if (params.get('register') === 'success') {
  alert("Registrasi berhasil! Silakan login.");
}