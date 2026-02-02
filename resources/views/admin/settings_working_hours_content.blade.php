<div style="width: 99%;">

    @include('admin.header_content')

    <div class="w-100" style="width: 100%; margin: 0 auto;" id="working_hours_div">

        {{-- Status Block --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        @if ($status['is_open'])
                            <h4><span class="badge" style="background-color: #28a745; color: #fff; padding: 8px 16px; font-size: 16px;">Бот зараз ПРАЦЮЄ</span></h4>
                        @else
                            <h4><span class="badge" style="background-color: #dc3545; color: #fff; padding: 8px 16px; font-size: 16px;">Бот зараз НЕ ПРАЦЮЄ</span></h4>
                            @if ($status['reason'])
                                <p class="mb-1"><strong>Причина:</strong> {{ $status['reason'] }}</p>
                            @endif
                            @if ($status['next_open'])
                                <p class="mb-0"><strong>Наступне відкриття:</strong> {{ date('d.m.Y H:i', strtotime($status['next_open'])) }}</p>
                            @endif
                        @endif
                    </div>
                    <div class="mt-2">
                        @if ($wh_settings['quick_pause_active'] == '1' && $wh_settings['quick_pause_end'] && strtotime($wh_settings['quick_pause_end']) > time())
                            <div class="text-center mb-2">
                                <small class="text-muted">Пауза до: {{ date('H:i', strtotime($wh_settings['quick_pause_end'])) }}</small>
                            </div>
                            <form action="{{ route('settings_working_hours_resume_pause') }}" method="post" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">Відновити роботу</button>
                            </form>
                        @else
                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#quickPauseModal">Швидка пауза</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs" id="workingHoursTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="schedule-tab" data-toggle="tab" href="#schedule" role="tab">Тижневий графік</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="closures-tab" data-toggle="tab" href="#closures" role="tab">Тимчасові закриття</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="wh-settings-tab" data-toggle="tab" href="#wh-settings" role="tab">Налаштування</a>
            </li>
        </ul>

        <div class="tab-content mt-3" id="workingHoursTabContent">

            {{-- Tab 1: Weekly Schedule --}}
            <div class="tab-pane fade show active" id="schedule" role="tabpanel">
                <form action="{{ route('settings_working_hours_schedule_save') }}" method="post">
                    @csrf
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 180px;">День</th>
                                <th style="width: 120px;">Робочий день</th>
                                <th style="width: 150px;">Відкриття</th>
                                <th style="width: 150px;">Закриття</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedule as $day)
                                <tr>
                                    <td>{{ $day_names[$day->day_of_week] }}</td>
                                    <td class="text-center">
                                        <input type="checkbox" name="is_working_day[{{ $day->day_of_week }}]" value="1" {{ $day->is_working_day ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control" name="open_time[{{ $day->day_of_week }}]" value="{{ substr($day->open_time, 0, 5) }}">
                                    </td>
                                    <td>
                                        <input type="time" class="form-control" name="close_time[{{ $day->day_of_week }}]" value="{{ substr($day->close_time, 0, 5) }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Зберегти графік</button>
                </form>
            </div>

            {{-- Tab 2: Temporary Closures --}}
            <div class="tab-pane fade" id="closures" role="tabpanel">
                <div class="mb-3">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#closureModal" onclick="resetClosureModal()">Додати закриття</button>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Початок</th>
                            <th>Кінець</th>
                            <th>Причина</th>
                            <th style="width: 80px;">Активне</th>
                            <th style="width: 150px;">Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($closures as $closure)
                            <tr class="{{ strtotime($closure->end_datetime) < time() ? 'text-muted' : '' }}">
                                <td>{{ date('d.m.Y H:i', strtotime($closure->start_datetime)) }}</td>
                                <td>{{ date('d.m.Y H:i', strtotime($closure->end_datetime)) }}</td>
                                <td>{{ $closure->reason ?? '—' }}</td>
                                <td class="text-center">{{ $closure->active ? 'Так' : 'Ні' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="editClosure({{ $closure->id }}, '{{ $closure->start_datetime }}', '{{ $closure->end_datetime }}', '{{ addslashes($closure->reason) }}', {{ $closure->active }})">Ред.</button>
                                    <form action="{{ route('settings_working_hours_closure_delete') }}" method="post" class="d-inline" onsubmit="return confirm('Видалити це закриття?')">
                                        @csrf
                                        <input type="hidden" name="closure_id" value="{{ $closure->id }}">
                                        <button type="submit" class="btn btn-sm btn-danger">Вид.</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Немає запланованих закриттів</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Tab 3: Settings --}}
            <div class="tab-pane fade" id="wh-settings" role="tabpanel">
                <form action="{{ route('settings_working_hours_settings_save') }}" method="post">
                    @csrf
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="width: 300px;">Модуль увімкнено</span>
                        </div>
                        <div class="form-control d-flex align-items-center">
                            <input type="checkbox" name="enabled" value="1" {{ $wh_settings['enabled'] == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="width: 300px;">Режим блокування</span>
                        </div>
                        <select class="form-control" name="blocking_mode">
                            <option value="orders_only" {{ $wh_settings['blocking_mode'] == 'orders_only' ? 'selected' : '' }}>Тільки замовлення</option>
                            <option value="full" {{ $wh_settings['blocking_mode'] == 'full' ? 'selected' : '' }}>Повне блокування бота</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <small class="text-muted">
                            <strong>Тільки замовлення</strong> — перегляд меню, кошик, навігація працюють; при спробі оформити замовлення — попередження.<br>
                            <strong>Повне блокування</strong> — на будь-яке повідомлення бот відповідає тільки повідомленням про неробочий час (крім /start).
                        </small>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="width: 300px;">Замовлення на інший час</span>
                        </div>
                        <div class="form-control d-flex align-items-center">
                            <input type="checkbox" name="allow_future_orders" value="1" {{ ($wh_settings['allow_future_orders'] ?? '1') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="form-group">
                        <small class="text-muted">
                            Дозволяє клієнтам оформлювати замовлення на інший день/час навіть у неробочий час. Клієнт зможе переглядати меню, додавати товари в кошик та обирати зручний час доставки.
                        </small>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="width: 300px;">Заголовок повідомлення</span>
                        </div>
                        <input type="text" class="form-control" name="message_title" value="{{ $wh_settings['message_title'] }}">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="width: 300px;">Текст повідомлення</span>
                        </div>
                        <textarea class="form-control" name="message_text" rows="4">{{ $wh_settings['message_text'] }}</textarea>
                    </div>
                    <div class="form-group">
                        <small class="text-muted">Підтримуються HTML-теги Telegram: &lt;b&gt;, &lt;i&gt;, &lt;a&gt;, &lt;code&gt;</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Зберегти налаштування</button>
                </form>
            </div>

        </div>

    </div>

</div>

{{-- Quick Pause Modal --}}
<div class="modal fade" id="quickPauseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('settings_working_hours_quick_pause') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Швидка пауза</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Тривалість</label>
                        <select class="form-control" name="pause_duration" id="pauseDuration" onchange="toggleCustomDuration()">
                            <option value="15">15 хвилин</option>
                            <option value="30" selected>30 хвилин</option>
                            <option value="60">1 година</option>
                            <option value="120">2 години</option>
                            <option value="custom">Довільна</option>
                        </select>
                    </div>
                    <div class="form-group" id="customDurationGroup" style="display: none;">
                        <label>Кількість хвилин</label>
                        <input type="number" class="form-control" name="custom_duration" min="1" max="1440" placeholder="Введіть кількість хвилин">
                    </div>
                    <div class="form-group">
                        <label>Причина (необов'язково)</label>
                        <input type="text" class="form-control" name="pause_reason" placeholder="Наприклад: Технічна перерва">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Скасувати</button>
                    <button type="submit" class="btn btn-warning">Активувати паузу</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Closure Add/Edit Modal --}}
<div class="modal fade" id="closureModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('settings_working_hours_closure_save') }}" method="post">
                @csrf
                <input type="hidden" name="closure_id" id="closureId" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="closureModalTitle">Додати закриття</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Початок</label>
                        <input type="datetime-local" class="form-control" name="start_datetime" id="closureStart" required>
                    </div>
                    <div class="form-group">
                        <label>Кінець</label>
                        <input type="datetime-local" class="form-control" name="end_datetime" id="closureEnd" required>
                    </div>
                    <div class="form-group">
                        <label>Причина (необов'язково)</label>
                        <input type="text" class="form-control" name="reason" id="closureReason" placeholder="Наприклад: Санітарний день">
                    </div>
                    <div class="form-group">
                        <label>Активне</label>
                        <select class="form-control" name="active" id="closureActive">
                            <option value="1">Так</option>
                            <option value="0">Ні</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Скасувати</button>
                    <button type="submit" class="btn btn-primary">Зберегти</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    function toggleCustomDuration() {
        var sel = document.getElementById('pauseDuration');
        var customGroup = document.getElementById('customDurationGroup');
        customGroup.style.display = sel.value === 'custom' ? 'block' : 'none';
    }

    function resetClosureModal() {
        document.getElementById('closureId').value = '';
        document.getElementById('closureStart').value = '';
        document.getElementById('closureEnd').value = '';
        document.getElementById('closureReason').value = '';
        document.getElementById('closureActive').value = '1';
        document.getElementById('closureModalTitle').textContent = 'Додати закриття';

        // Set default dates: now to +2 hours
        var now = new Date();
        var later = new Date(now.getTime() + 2 * 60 * 60 * 1000);
        document.getElementById('closureStart').value = formatDatetimeLocal(now);
        document.getElementById('closureEnd').value = formatDatetimeLocal(later);
    }

    function editClosure(id, start, end, reason, active) {
        document.getElementById('closureId').value = id;
        document.getElementById('closureStart').value = formatDatetimeLocal(new Date(start));
        document.getElementById('closureEnd').value = formatDatetimeLocal(new Date(end));
        document.getElementById('closureReason').value = reason;
        document.getElementById('closureActive').value = active;
        document.getElementById('closureModalTitle').textContent = 'Редагувати закриття';
        $('#closureModal').modal('show');
    }

    function formatDatetimeLocal(date) {
        var y = date.getFullYear();
        var m = ('0' + (date.getMonth() + 1)).slice(-2);
        var d = ('0' + date.getDate()).slice(-2);
        var h = ('0' + date.getHours()).slice(-2);
        var min = ('0' + date.getMinutes()).slice(-2);
        return y + '-' + m + '-' + d + 'T' + h + ':' + min;
    }
</script>
