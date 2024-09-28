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
                        <a href="/{{ app()->getlocale() }}/admin/users/{{ $user->id }}/edit" class="btn btn-warning">Edit</a>
                        <form action="/{{ app()->getlocale() }}/admin/users/{{ $user->id }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#users-table').DataTable();
    });
</script>
@endsection
