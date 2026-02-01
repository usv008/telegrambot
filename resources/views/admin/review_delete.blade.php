<div class="starter-template mt-2">
    <form class="review_edit_form p-4" action="{{ route('review_delete_yes') }}" method="post">
        <div class="form-group">
            <label>Вы действительно хотите удалить этот отзыв?</label>
            <input type="hidden" name="action" value="review_delete">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="id" value="{{ $id }}">
            <button type="submit" class="btn btn-danger mb-2 mt-2 w-100">Удалить</button>
        </div>
    </form>
</div>
