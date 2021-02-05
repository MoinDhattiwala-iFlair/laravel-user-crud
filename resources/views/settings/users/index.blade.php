@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-center">
                    <span class="card-title h1">{{ __('Users') }}</span>
                    <span class="pull-right">
                        <button class="btn btn-success float-right" onclick="showBasicModal('Add User', '{{ route('user.create') }}');">Add</button>
                    </span>
                </div>
                <div class="card-body">
                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table id="user-table" class="table table-border">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>Country</th>
                                    <th>Registerd At</th>
                                    <th>Updated At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @forelse ($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                <td> <img src="{{ $user->avtar }}" class="rounded-circle" alt="{{ $user->name }}" /></td>

                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at->diffForHumans() }}</td>
                                <td>{{ $user->updated_at->diffForHumans() }}</td>
                                <td><button class="btn btn-warning" onclick="showBasicModal('Edit User', '{{ route('user.edit', $user->id) }}');">Edit</button>
                                </td>
                                <td><button class="btn btn-danger" onclick="deleteRecord({{ $user->id }}, '{{route('user.destroy', $user->id)}}')">Delete</button></td>
                                </tr>
                                @empty

                                @endforelse --}}
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
@push('js')
<script type="text/javascript">
    var table;
    $(document).ready(function() {
        table = $('#user-table').DataTable({

            processing: true
            , serverSide: true
            , ajax: '{{ route("user.index") }}'
            , "order": [
                [2, "asc"]
            ]
            , columns: [{
                    data: 'DT_RowIndex'
                    , name: 'DT_RowIndex'
                    , orderable: false
                    , searchable: false

                }
                , {
                    data: 'photo'
                    , name: 'photo'
                }
                , {
                    data: 'name'
                    , name: 'name'
                }
                , {
                    data: 'email'
                    , name: 'email'
                }
                , {
                    data: 'city'
                    , name: 'cities.name'
                }
                , {
                    data: 'state'
                    , name: 'states.name'
                }, {
                    data: 'country'
                    , name: 'countries.name'
                }
                , {
                    data: 'created_at'
                    , name: 'created_at'
                    , orderable: false
                    , searchable: false
                }
                , {
                    data: 'updated_at'
                    , name: 'updated_at'
                    , orderable: false
                    , searchable: false
                }
                , {
                    data: 'action'
                    , name: 'action'
                    , orderable: false
                    , searchable: false
                }
            , ]
        });

    });

</script>
@include('csc::csc_js')
@endpush
