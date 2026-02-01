<select name="select_user" id="select_users" class="form-control">
@foreach($users as $user)
    <option value="{{ $user->user_id }}">{{ $user->first_name }} {{ $user->last_name }}{{ isset($user->username) && $user->username !== '' ? ' ('.$user->username.')' : '' }}{{ isset($user->lang) &&$user->lang !== '' ? ' ['.$user->lang.']' : '' }} - {{ date("d.m.Y H:i:s", strtotime($user->updated_at)) }}</option>
@endforeach
</select>
