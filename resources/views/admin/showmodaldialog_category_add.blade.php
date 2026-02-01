{!! Form::open(['url'=>route('categories_add'), 'class'=>'form-horizontal', 'method'=>'POST', 'enctype'=>'multipart/form-data']) !!}
<div class="modal-header">
    <h5 class="modal-title" id="modal_header">{{ $title }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" id="modal_body">
    <div class="form-group">
        <h5>Название</h5>
        <div class="col-xs-8">
            {!! Form::text('name', old('name'), ['class'=>'form-control', 'placeholder'=>'Введите название категории']) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::file('image[]', ['class'=>'filestyle', 'data-buttonText'=>"Выберите изображение", 'data-buttonName'=>"btn-primary", 'data-placeholder'=>"Файла нет", 'accept'=>'image/jpeg,image/png,image/gif', 'multiple'=> 'multiple']) !!}
        {!! Form::hidden('action', $data['action']) !!}
    </div>
</div>
<div class="modal-footer" id="modal_footer">
    {!! Form::button('Добавить', ['class'=>'btn btn-success', 'type'=>'submit']) !!}
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
</div>
{!! Form::close() !!}

<script type="text/javascript">
    $(function () {
        $(":file").filestyle({btnClass: "btn-primary", text: "Выберите изображение", placeholder: "Файла нет"});
    });
</script>
