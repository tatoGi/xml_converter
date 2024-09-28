<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ __('messages.bank_statement_convert') }}</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="{{ asset('storage/logo/restosoft_logo.jpg') }}" type="image/x-icon">
</head>
<body class="d-flex flex-column vh-100">

<!-- Header with logo -->
<header class="container text-center mb-4 mt-4">
  <img src="{{ asset('storage/logo/restosoft_logo.jpg') }}" alt="{{ __('messages.logo_alt') }}" class="img-fluid" style="max-width: 200px;">
</header>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                  <h5 class="mb-0">{{ __('messages.bank_statement_convert') }}</h5>
                </div>
                <div class="card-body">
                  <h6 class="card-title">{{ __('messages.upload_file') }}</h6>
                  <form action="{{ route('file.upload', [app()->getLocale()]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="inn">{{ __('messages.inn') }}</label>
                        <input class="form-control" name="file_name" type="text" id="inn" value="{{ Auth::user()->inn }}" readonly>
                      </div>

                    <div class="mb-3">
                      <input class="form-control" name="INN" type="file" id="fileUpload" required accept=".txt,.xml">
                      <div class="mt-2 text-success" id="fileName"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('messages.upload_and_convert') }}</button>
                  </form>
                  <div id="alertMessage" class="mt-3" style="display: none;"></div>
                </div>
              </div>
              <ul class="d-flex list-unstyled">
                @foreach (locales() as $locale => $data)
                    @php
                        $isActive = app()->getLocale() === $locale ? 'btn-primary' : 'btn-light';
                        $langName = $locale === 'en' ? __('messages.english') : ($locale === 'ru' ? __('messages.russian') : __('messages.georgian'));
                    @endphp
                    <li class="me-2">
                        <a href="{{ $data }}" class="btn {{ $isActive }} btn-sm">
                            {{ $langName }}
                        </a>
                    </li>
                @endforeach
            </ul>

        </div>
    </div>
</div>

<!-- Footer with text -->
<footer class="container text-center mt-4">
  <p class="text-muted">&copy; 2024 {{ __('messages.made_by') }} Restosoft</p>
</footer>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Script to Display File Name -->
<script>
    document.getElementById('fileUpload').addEventListener('change', function() {
      var fileName = this.files[0].name;
      document.getElementById('fileName').innerHTML = '{{ __('messages.selected_file') }}: ' + fileName;

      window.originalFileName = fileName; // Store in a global variable
    });

    function showAlert(message, isError = false) {
      const alertDiv = document.getElementById('alertMessage');
      alertDiv.innerHTML = message;
      alertDiv.className = isError ? 'alert alert-danger' : 'alert alert-success';
      alertDiv.style.display = 'block';
    }

    document.querySelector('form').addEventListener('submit', function(event) {
      event.preventDefault();

      const formData = new FormData(this);
      fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        }
      })
      .then(response => {
        if (response.ok) {
          return response.blob();
        } else {
          throw new Error('{{ __('messages.file_upload_failed') }}');
        }
      })
      .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        const originalFileName = window.originalFileName;

        a.href = url;
        a.download = originalFileName.replace(/\.[^/.]+$/, "") + '.txt';
        document.body.appendChild(a);
        a.click();
        a.remove();

        document.querySelector('form').reset();
        document.getElementById('fileName').innerHTML = '';

        showAlert('{{ __('messages.file_uploaded_success') }}', false);
      })
      .catch(error => {
        showAlert(error.message, true);
      });
    });
</script>

<style>
    img {
        border-radius: 20%;
        box-sizing: border-box;
    }
</style>
</body>
</html>
