@if($users->count())
    @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                <span class="badge bg-secondary text-uppercase">
                    {{ $user->role }}
                </span>
            </td>
            <td>{{ optional($user->created_at)->format('M d, Y') }}</td>
            <td class="d-flex gap-2">
                <button
                    type="button"
                    class="btn btn-sm btn-info text-white btn-view-user"
                    data-id="{{ $user->id }}"
                >
                    View
                </button>

                <button
                    type="button"
                    class="btn btn-sm btn-warning btn-edit-user"
                    data-id="{{ $user->id }}"
                    data-name="{{ $user->name }}"
                    data-email="{{ $user->email }}"
                    data-role="{{ $user->role }}"
                    data-update-url="{{ route('admin.users.update', $user) }}"
                >
                    Update
                </button>

                <button
                    type="button"
                    class="btn btn-sm btn-danger btn-delete-user"
                    data-id="{{ $user->id }}"
                    data-name="{{ $user->name }}"
                    data-delete-url="{{ route('admin.users.destroy', $user) }}"
                >
                    Delete
                </button>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="6" class="text-center">No users found.</td>
    </tr>
@endif