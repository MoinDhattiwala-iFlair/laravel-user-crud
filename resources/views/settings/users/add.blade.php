@if (isset($user->id))
{!! Form::open([
'url' => route('user.update', $user->id),
'method' => 'PUT',
'files'=> true,
'id' => 'saveBasicModalFrm',
]) !!}
@else
{!! Form::open(['url' => route('user.store'), 'method' => 'POST', 'files'=> true, 'id' => 'saveBasicModalFrm'])!!}
@endif
<fieldset>
    <div class="form-group row text-center">
        <div class="col-12">
            <img src="{{ $user->avtar ?? '' }}" id="user_avtar" class="rounded-circle" alt="{{ $user->name ?? '' }}"
                style="height: 100px; width:100px;" />
        </div>
        <div class="col-12 mt-2">
            {!! Form::file('photo', ['class' => '', 'id' => 'photo', 'onchange'=>'readURL(this, "user_avtar")']) !!}
        </div>
        <span class="text-danger error" role="alert" id="photo-error">
    </div>
    <div class="form-group">
        <label for="name">Name</label>
        {!! Form::text('name', $user->name ?? '', [
        'class' => 'form-control',
        'id' => 'name',
        'placeholder' => 'Enter name',
        ]) !!}
        <span class="text-danger error" role="alert" id="name-error">
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        {!! Form::email('email', $user->email ?? '', [
        'class' => 'form-control',
        'id' => 'email',
        'placeholder' => 'Enter email',
        ]) !!}
        <span class="text-danger error" role="alert" id="email-error">
    </div>
    <div class="form-group">
        <label for="csc_country">Country</label>
        {!! Form::select(null, [], null, ['class' => 'form-control','id' => 'csc_country', 'onchange' =>
        'getState(this.value)']) !!}

    </div>
    <div class="form-group">
        <label for="csc_state">State</label>
        {!! Form::select(null, [], null, ['class' => 'form-control','id' => 'csc_state', 'onchange' =>
        'getCity(this.value)']) !!}

    </div>
    <div class="form-group">
        <label for="city_id">City</label>
        {!! Form::select('city_id', [], $user->city_id ?? 0, ['class' => 'form-control','id' => 'csc_city']) !!}
        <span class="text-danger error" role="alert" id="city_id-error">
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        {!! Form::password('password', [
        'class' => 'form-control',
        'id' => 'password',
        'placeholder' => 'Enter password',
        ]) !!}
        <span class="text-danger error" role="alert" id="password-error">
    </div>
    <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        {!! Form::password('password_confirmation', [
        'class' => 'form-control',
        'id' => 'password_confirmation',
        'placeholder' => 'Confirm Password',
        ]) !!}
        <span class="text-danger error" role="alert" id="password_confirmation-error">
    </div>
    <hr />
    <div class="form-group float-right">
        <button type="save" class="btn btn-success">Save</button>
        <button type="reset" class="btn btn-danger">Reset</button>
    </div>
</fieldset>
{!! Form::close() !!}
<script>
    getCountry();
    @if (isset($user -> id))
        getState({{ $user->country()->id }});
        getCity({{ $user->state()->id }});
        $(document).ajaxStop(function () {
            $('#csc_country option[value="{{ $user->country()->id }}"]').attr('selected', true);            
            $('#csc_state option[value="{{ $user->state()->id }}"]').attr('selected', true);
            $('#csc_city option[value="{{ $user->city_id }}"]').attr('selected', true);
        });
    @endif
</script>