<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('storage/admin/style.css') }}">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <title>Login to Admin Panel</title>
    <!-- Add debugging script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const formToken = document.querySelector('input[name="_token"]');

            console.log('Page loaded');
            console.log('CSRF Meta Token:', csrfToken ? csrfToken.content : 'Not found');
            console.log('Form Token:', formToken ? formToken.value : 'Not found');
            console.log('Form action:', form.action);

            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent form submission for debugging
                console.log('Form submission intercepted');
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);
                console.log('CSRF Token:', formToken.value);
                console.log('Form data:', new FormData(form));

                // Now submit the form
                form.submit();
            });
        });
    </script>
    <!-- Add CSRF meta tag -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap CSS -->


</head>
<body class="d-flex flex-column vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg mt-5">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Login To Admin Panel</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form action="{{ route('admin.login.submit', ['locale' => app()->getLocale()]) }}" method="POST">
                        @csrf
                        <!-- Add hidden debug field -->
                        <input type="hidden" name="debug_token" value="{{ csrf_token() }}">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="password" required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <span id="togglePasswordIcon" class="bi bi-eye"></span>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and Popper.js -->
<script>
    // JavaScript to toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        // Toggle the input type
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    });
</script>

</body>
</html>
