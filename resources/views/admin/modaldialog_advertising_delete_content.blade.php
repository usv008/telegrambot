<div class="text-center">
    {!! Form::open(['url'=>route('stat_advertising_delete'), 'id' =>'form_add', 'class'=>'form-horizontal', 'method'=>'POST', 'enctype'=>'multipart/form-data']) !!}
    <h5>Точно удалить этот канал - {{ $name }} ({{ $url }})?</h5>
    {!! Form::hidden('channel_id', $channel_id) !!}
    <br />
    {!! Form::button('Да, удалить!', ['class'=>'btn btn-danger', 'type'=>'submit']) !!}
    {!! Form::close() !!}
</div>
