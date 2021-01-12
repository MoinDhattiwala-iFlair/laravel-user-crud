@if (isset($user->id))
    {!! Form::open([
    'url' => route('user.update', $user->id),
    'method' => 'PUT',
    'onsubmit' => 'return saveBasicModalFrm(this);',
    ]) !!}
@else
    {!! Form::open(['url' => route('user.store'), 'method' => 'POST', 'onsubmit' => 'return saveBasicModalFrm(this);'])
    !!}
@endif
<fieldset>
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
