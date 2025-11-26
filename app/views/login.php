<?php
if (!defined('APP_INIT')) { http_response_code(403); exit('Forbidden'); }
// app/views/login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CampusLite ERP</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center">
                    <h4>Login</h4>
                </div>
                <div class="card-body">
                    <form id="loginForm" method="post" action="/api/auth.php?action=login">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('loginForm');
    if (!form) return;
    form.addEventListener('submit', function(e){
        e.preventDefault();
        const url = form.getAttribute('action');
        const fd = new FormData(form);
        fetch(url, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(json => {
            if (json && json.status) {
                // Save last page to load after redirect (AJAX loader will handle it)
                try { window.localStorage.setItem('lastPage', 'dashboard'); } catch(e){}
                // Redirect to index.php (AJAX loader will load dashboard content and URL will remain index.php)
                window.location.href = 'index.php';
            } else {
                const msg = (json && json.message) ? json.message : 'Login failed';
                alert(msg);
            }
        }).catch(err => {
            console.error('Login error', err);
            // fallback to regular submit if AJAX fails
            form.submit();
        });
    });
});
</script>
</body>
</html>
