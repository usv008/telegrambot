<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Title of the document</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link rel="stylesheet" href="{{ asset ('assets/css/jquery-ui.css') }}">
    <script src="{{ asset ('assets/js/jquery-1.12.4.js') }}"></script>
    <script src="{{ asset ('assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset ('assets/js/popper.min.js') }}" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="{{ asset ('assets/js/bootstrap.min.js') }}" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <link rel="stylesheet" type="text/css" href="{{ asset ('assets/css/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset ('assets/css/dataTables.bootstrap4.min.css') }}">
    <script type="text/javascript" charset="utf8" src="{{ asset ('assets/js/jquery.dataTables.js') }}"></script>
    <script type="text/javascript" charset="utf8" src="{{ asset ('assets/js/dataTables.bootstrap4.min.js') }}"></script>
    <style>
        body{
            /*color: var(--tg-theme-text-color);*/
            color: #222222;
            /*background: var(--tg-theme-bg-color);*/
            background: #ffffff;
            /*border-color: var(--tg-theme-bg-color);*/
            border-color: #222222;
            /*display: flex;*/
            /*flex-direction: column;*/
            align-items: center;
            font-size: 18px;
        }

        .hint{
            /*color: var(--tg-theme-hint-color);*/
            color: #6f42c1;
        }

        .link{
            /*color: var(--tg-theme-link-color);*/
            color: #1d68a7;
        }

        .button{
            /*background: var(--tg-theme-button-color);*/
            background: #1c7430;
            /*color: var(--tg-theme-button-text-color);*/
            color: #ffffff;
            border: none;
            font-size: 18px;
        }

        .button:not(:last-child){
            margin-bottom: 20px
        }

        #usercard{
            text-align: center;
        }
    </style>
</head>

<body style="text-align: center; width: 100%; height: 100%;">

<div id="categories" style="min-width: 100%; min-height: 50px; display: flex; overflow-x: auto;">
{{--    @include("telegram.categories")--}}
</div>

{{--<div id="usercard"></div>--}}

{{--<p>Just text</p> <!--Просто текст для проверки-->--}}
{{--<a class="link" href="https://google.com">Google</a> <!--Просто ссылка для проверки-->--}}
{{--<p class="hint">Some little hint</p> <!--Просто текст-подсказка для проверки-->--}}
{{--<button id="btn" class="button">Show/Hide Main Button</button> <!--Кнопка, чтобы скрыть / показать основную кнопку-->--}}
{{--<button id="btnED" class="button">Enable/Disable Main Button</button> <!--Кнопка, чтобы сделать кнопку активной/неактивной-->--}}

<!-- Button trigger modal -->
{{--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">--}}
{{--    Всплывашка--}}
{{--</button>--}}
<div id="products">
{{--    @include("telegram.products")--}}
</div>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    let tg = window.Telegram.WebApp;
    tg.expand();

    // tg.MainButton.text = "Changed Text"; //изменяем текст кнопки
    // tg.MainButton.setText("Changed Text1"); //изменяем текст кнопки иначе
    tg.MainButton.textColor = "#ffffff"; //изменяем цвет текста кнопки
    tg.MainButton.color = "#1c7430"; //изменяем цвет бэкграунда кнопки
    tg.MainButton.setParams({"color": "#1c7430"}); //так изменяются все параметры

    // btn.addEventListener('click', function(){ //вешаем событие на нажатие html-кнопки
    //     if (tg.MainButton.isVisible){ //если кнопка показана
    //         tg.MainButton.hide() //скрываем кнопку
    //     }
    //     else{ //иначе
    //         tg.MainButton.show() //показываем
    //     }
    // });
    //
    // let btnED = document.getElementById("btnED"); //получаем кнопку активировать/деактивировать
    // btnED.addEventListener('click', function(){ //вешаем событие на нажатие html-кнопки
    //     if (tg.MainButton.isActive){ //если кнопка показана
    //         tg.MainButton.setParams({"color": "#89a57f"}); //меняем цвет
    //         tg.MainButton.textColor = "#666666"
    //         tg.MainButton.disable() //скрываем кнопку
    //     }
    //     else{ //иначе
    //         tg.MainButton.setParams({"color": "#1c7430"}); //меняем цвет
    //         tg.MainButton.textColor = "#ffffff"
    //         tg.MainButton.enable() //показываем
    //     }
    // });

    let usercard = document.getElementById("usercard"); //получаем блок usercard

    // let profName = document.createElement('p'); //создаем параграф
    // profName.innerText = `${tg.initDataUnsafe.user.first_name}
    // ${tg.initDataUnsafe.user.last_name}
    // ${tg.initDataUnsafe.user.username} (${tg.initDataUnsafe.user.language_code})`;
    //выдем имя, "фамилию", через тире username и код языка
    // usercard.appendChild(profName); //добавляем

    // let userid = document.createElement('p'); //создаем еще параграф
    // userid.innerText = `${tg.initDataUnsafe.user.id}`; //показываем user_id
    // usercard.appendChild(userid); //добавляем

    $(document).ready( function () {
        $.ajax({
            type: "GET",
            url: "{{ route('get_categories') }}",
            data: "category_select={{ $category_select }}",
            cache: false
        }).done(function(categories_data) {
            $("#categories").html(categories_data);
            $.ajax({
                type: "GET",
                url: "{{ route('get_products') }}",
                data: "category_select={{ $category_select }}",
                cache: false
            }).done(function(products_data) {
                $("#products").html(products_data);
            }).fail(function() {
                $("#products").html("Произошла ошибка");
            });
        }).fail(function() {
            $("#categories").html("Произошла ошибка");
        });


    });
</script>

</body>

</html>
