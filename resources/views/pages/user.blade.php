@extends('layout.main')

@push('script')
    <script>
        $(document).on('click', '.btn-edit', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const email = $(this).data('email');
            const phone = $(this).data('phone');
            const status = $(this).data('status');

            // Mengubah form action untuk memastikan update terjadi pada user yang tepat
            $('#editModal form').attr('action', '/user/' + id);

            // Mengisi field di modal dengan data yang sesuai
            $('#editModal #id').val(id);
            $('#editModal #name').val(name);
            $('#editModal #email').val(email);
            $('#editModal #phone').val(phone);
            $('#editModal #status').val(status);  // Menetapkan status yang ada
        });
    </script>
@endpush

@section('content')
    <x-breadcrumb :values="[__('menu.users')]">
        <button
            type="button"
            class="btn btn-primary btn-create"
            data-bs-toggle="modal"
            data-bs-target="#createModal">
            {{ __('menu.general.create') }}
        </button>
    </x-breadcrumb>

    <div class="card mb-5">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('model.user.name') }}</th>
                        <th>{{ __('model.user.email') }}</th>
                        <th>{{ __('model.user.phone') }}</th>
                        <th>{{ __('model.user.status') }}</th>
                        <th>{{ __('menu.general.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>
                                <span class="badge bg-label-primary me-1">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-info btn-sm btn-edit"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-phone="{{ $user->phone }}"
                                        data-status="{{ $user->status }}"  <!-- Menambahkan status -->
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal">
                                    {{ __('menu.general.edit') }}
                                </button>
                                <form action="{{ route('user.destroy', $user) }}" class="d-inline" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">{{ __('menu.general.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {!! $data->appends(['search' => $search])->links() !!}

    <!-- Modal Create -->
    <div class="modal fade" id="createModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="post" action="{{ route('user.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalTitle">{{ __('menu.general.create') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <x-input-form name="name" :label="__('model.user.name')"/>
                    <x-input-form name="email" :label="__('model.user.email')" type="email"/>
                    <x-input-form name="phone" :label="__('model.user.phone')"/>
                    <x-input-form name="password" :label="__('password')" type="password"/>
                    <x-input-form name="password_confirmation" :label="__('konfirmasi password')" type="password"/>

                    <div class="mb-3">
                        <label for="status" class="form-label">{{ __('model.user.status') }}</label>
                        <select name="status" id="status" class="form-select">
                            <option value="aktif">Aktif</option>
                            <option value="non aktif">Non Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('menu.general.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">{{ __('menu.general.save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="post" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalTitle">{{ __('menu.general.edit') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="id" value="">
                    <x-input-form name="name" :label="__('model.user.name')"/>
                    <x-input-form name="email" :label="__('model.user.email')" type="email"/>
                    <x-input-form name="phone" :label="__('model.user.phone')"/>
                    <x-input-form name="password" :label="__('password baru')" type="password"/>
                    <x-input-form name="password_confirmation" :label="__('konfirmasi password')" type="password"/>

                    <div class="mb-3">
                        <label for="status" class="form-label">{{ __('model.user.status') }}</label>
                        <select name="status" id="status" class="form-select">
                            <option value="aktif">Aktif</option>
                            <option value="non aktif">Non Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('menu.general.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">{{ __('menu.general.update') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
