@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="card p-4">
        <div class="header">
            <h1>Manage Users</h1>
        </div>
        <div class="add_user">
            <a href="/{{ app()->getlocale() }}/admin/users/create" class="btn btn-primary mb-3">Add User</a>
        </div>
        <div class="table-responsive">
            <table id="users-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Inn</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->inn }}</td>
                        <td>
                            <a href="{{ route('users.edit', [app()->getLocale(), $user->id]) }}" class="btn btn-warning edit-user">Edit</a>
                            <button class="btn btn-danger delete-user" data-token="{{ csrf_token() }}" data-user-id="{{ $user->id }}" data-route="{{ route('users.destroy', ['locale' => app()->getLocale(), 'user' => $user->id]) }}">
                                Delete
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize the DataTable
        const table = new DataTable('#users-table', {
            responsive: true, // Enable responsive behavior
        });

        // Handle click event on delete-user buttons
        document.querySelectorAll('.delete-user').forEach(button => {
    button.addEventListener('click', function () {
        const userId = this.getAttribute('data-user-id');
        const ajaxUrl = this.getAttribute('data-route');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        if (confirm('Are you sure you want to delete this user?')) {
            // Use Fetch API for DELETE request
            fetch(ajaxUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: userId }) // Send the user ID in the body
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                alert('User deleted successfully.');
                console.log(data);

                // Remove the deleted user row from the table
                button.closest('tr').remove();
            })
            .catch(error => {
                alert('Error deleting user.');
                console.error('Error:', error);
            });
        }
    });
});

    });
</script>
@endsection
