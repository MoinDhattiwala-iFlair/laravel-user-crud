@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">{{ __('Users') }}</span>
                        <div class="pull-right">
                            <button class="btn btn-success float-right"
                                onclick="showBasicModal('Add User', '{{ route('user.create') }}');">Add</button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-border">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Registerd At</th>
                                        <th>Updated At</th>
                                        <th colspan="2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $user)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->created_at->diffForHumans() }}</td>
                                            <td>{{ $user->updated_at->diffForHumans() }}</td>
                                            {{-- <td><button class="btn btn-info">View</button>
                                            </td> --}}
                                            <td><button class="btn btn-warning"
                                                    onclick="showBasicModal('Edit User', '{{ route('user.edit', $user->id) }}');">Edit</button>
                                            </td>
                                            <td><button class="btn btn-danger">Delete</button></td>
                                        </tr>
                                    @empty

                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Basic Modal Start --}}
    @include('modals.basicmodal')
@endsection
