<section class="product">

    <div class="product_main">
        <div class="product_img">
            <img src="{{ asset('/assets/img/thumb/'.$abra_kadabra.$product->id_product.'.webp') }}" alt="" class="img">
        </div>
        <div class="product_desc">
            <div class="product_title">{{ $product->name }}</div>
            <div class="product_ingredients">
                {{ $product->product_features && $product->product_features->value ? $product->product_features->value : '' }}
            </div>
            @if($product_specific_price->count() > 0)
                <br />Стара ціна <del>{{ bcmul($product->product_attributes->first()->price, 1, 2) }} грн</del>
            @endif
            <div class="product_btn">
            <!-- <button class="button" id="addProduct" data-product-id="{{ $product->id_product }}" data-combination-id="{{ $product->product_attributes->first()->id_product_attribute }}" data-price="{{ bcmul($product->product_attributes->first()->price, 1, 2) }}" data-price-all="{{ bcmul($product->product_attributes->first()->price, 1, 2) }}">додати за {{ bcmul($product->product_attributes->first()->price, 1, 2) }} грн</button> -->
                <button id="addProduct" class="ready addProductClass" data-product-id="{{ $product->id_product }}" data-combination-id="{{ $product->product_attributes->first()->id_product_attribute }}" data-price="{{ bcmul($product->product_attributes->first()->price_new, 1, 2) }}" data-price-all="{{ bcmul($product->product_attributes->first()->price_new, 1, 2) }}">
                    <div class="message submitMessage">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13 12.2">
                            <polyline stroke="currentColor" points="2,7.1 6.5,11.1 11,7.1 " />
                            <line stroke="currentColor" x1="6.5" y1="1.2" x2="6.5" y2="10.3" />
                        </svg>
                        <span id="addProduct_price_text" class="button-text">додати за {{ bcmul($product->product_attributes->first()->price_new, 1, 2) }} грн</span>
                    </div>

                    <div class="message loadingMessage">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19 17">
                            <circle class="loadingCircle" cx="2.2" cy="10" r="1.6" />
                            <circle class="loadingCircle" cx="9.5" cy="10" r="1.6" />
                            <circle class="loadingCircle" cx="16.8" cy="10" r="1.6" />
                        </svg>
                    </div>

                    <div class="message successMessage">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13 11">
                            <polyline stroke="currentColor" points="1.4,5.8 5.1,9.5 11.6,2.1 " />
                        </svg>
                        <span class="button-text">додано!</span>
                    </div>
                </button>
                <canvas id="canvas" class="convasClass"></canvas>

            </div>
        </div>
    </div>
    @if (isset($product->product_attributes))
        <div class="product_D">
            @if ($product->id_category_default == 3)
                <div class="product_D__title">Діаметр</div>
            @else
                <div class="product_D__title">Розмір / Вага</div>
            @endif
            <div class="product_D__list">
                @foreach($product->product_attributes as $key => $combination)
                    <div class="product_D__item{{ $key === array_key_first($product->product_attributes->toArray())  ? ' active' : ''}}" data-id="{{ $combination->id_product_attribute }}" data-combination-options-id="{{ $combination->id_attribute }}" data-price="{{ bcmul($combination->price, 1, 2) }}">{{ $combination->name }}</div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($categories->count() > 0)
        <div class="product_add-ing">
            <div class="product_add-ing__title">Додати по смаку</div>
            <div class="product_add-ing__list">
                @foreach($categories as $category)
                    @if ($category->id_accessory_group == 1)
                        <div class="product_add-ing__item borts_big" style="">
                            <div class="item_name"><span class="item_icon">+</span>{{ $category->category_name }}</div>
                            <div class="item_ing__list">
                                @foreach($category->products as $item)
                                    @if($item->product->id_product == 477 || $item->product->id_product == 175 || $item->product->id_product == 43)
                                        <div class="item_ing">
                                            <div class="item_ing__name">{{ $item->product->name }}</div>
                                            <div class="item__count ">
                                                <div class="count_min">-</div>
                                                <input type="number" name="count_input" class="ingredients_inputs borts_inputs" readonly max="1" value="0" id="{{ $item->product->id_product }}" data-id="{{ $item->product->id_product }}" data-category-id="{{ $item->product->id_category_default }}" data-combination-id="{{ $item->product_attributes->id_product_attribute }}" data-name="{{ $item->product->name }}" data-price="{{ $item->product_attributes->price }}">
                                                <div class="count_plus borts_count_plus">+</div>
                                            </div>
                                            <div class="item_ing__price">{{ bcmul($item->product_attributes->price, 1, 2) }} ₴</div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="product_add-ing__item borts_small" style="">
                            <div class="item_name"><span class="item_icon">+</span>{{ $category->category_name }}</div>
                            <div class="item_ing__list">
                                @foreach($category->products->sortBy('id_accessory_group_product') as $item)
                                    @if($item->product->id_product == 176 || $item->product->id_product == 478 || $item->product->id_product == 479)
                                        <div class="item_ing">
                                            <div class="item_ing__name">{{ $item->product->name }}</div>
                                            <div class="item__count">
                                                <div class="count_min borts_count_min">-</div>
                                                <input type="number" name="count_input" class="ingredients_inputs borts_inputs" readonly max="1" value="0" id="{{ $item->product->id_product }}" data-id="{{ $item->product->id_product }}" data-category-id="{{ $item->product->id_category_default }}" data-combination-id="{{ $item->product_attributes->id_product_attribute }}" data-name="{{ $item->product->name }}" data-price="{{ $item->product_attributes->price }}">
                                                <div class="count_plus borts_count_plus">+</div>
                                            </div>
                                            <div class="item_ing__price">{{ bcmul($item->product_attributes->price, 1, 2) }} ₴</div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="product_add-ing__item">
                            <div class="item_name"><span class="item_icon">+</span>{{ $category->category_name }}</div>
                            <div class="item_ing__list">
                                @foreach($category->products as $item)
                                    <div class="item_ing">
                                        <div class="item_ing__name">{{ $item->product->name }}</div>
                                        <div class="item__count">
                                            <div class="count_min">-</div>
                                            <input type="number" name="count_input" class="ingredients_inputs" readonly max="4" value="0" id="{{ $item->product->id_product }}" data-id="{{ $item->product->id_product }}" data-category-id="{{ $item->id_category_default }}" data-combination-id="{{ $item->product_attributes->id_product_attribute }}" data-name="{{ $item->product->name }}" data-price="{{ $item->product_attributes->price }}">
                                            <div class="count_plus">+</div>
                                        </div>
                                        <div class="item_ing__price">{{ bcmul($item->product_attributes->price, 1, 2) }} ₴</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</section>

<script>
    var price = {{ bcmul($product->product_attributes->first()->price, 1, 2) }};

    function hide_show_borts() {
        var elems = $(".product_D__item");
        var elemsTotal = elems.length;
        for (var i=0; i < elemsTotal; i++) {
            if ($(elems[i]).hasClass('active')) {
                // alert($(elems[i]).attr('data-combination-options-id'));
                var inputs = $(".ingredients_inputs");
                for (var n = 0; n < inputs.length; n++) {
                    $(inputs[n]).val(0);
                }
                if ($(elems[i]).attr('data-combination-options-id') == 9) {
                    $(".borts_big").hide();
                    $(".borts_small").show();
                }
                else if ($(elems[i]).attr('data-combination-options-id') == 8) {
                    $(".borts_small").hide();
                    $(".borts_big").show();
                }
                else {
                    $(".borts_small").hide();
                    $(".borts_big").hide();
                }
            }
        }
    }

    $(document).ready( function(){

        const button_close = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

        hide_show_borts();

        $('.product_D__item').on('click', function(){
            if($(this).hasClass('active')) return;

            $('.product_D__item').removeClass('active');
            $(this).addClass('active');
            $('#productDInput').val($(this).attr('data-id'));
            $('#addProduct').attr('data-combination-id', $(this).attr('data-id'));
            $('#addProduct').attr('data-price', $(this).attr('data-price'));
            $('#addProduct_price_text').html('додати за '+$(this).attr('data-price')+' грн');
            // $('#addProduct').text('додати за '+$(this).attr('data-price')+' грн');
            price = $(this).attr('data-price');
            hide_show_borts();
            button = document.getElementById('addProduct');
            render();
        });

        // $('.product_add-ing__item .item_name').on('click', function(){
        //     $(this).next().slideToggle();
        // });

        $('.product_add-ing__item .item_name').on('click', function(){
            $(this).toggleClass('open');

            if ($(this).hasClass('open')){
                $(this).find('.item_icon').text('-');
            } else{
                $(this).find('.item_icon').text('+');
            }
            $(this).next().slideToggle();
        });

        $('.item__count .count_min').on('click', function(){
            var input = $(this).parent().find('input');
            var inputVal = parseInt(input.val());

            if(inputVal == 0){
                return;
            } else{
                var n = inputVal - 1;
                input.val(n);
            }
            price = parseFloat(price) - parseFloat(input.attr('data-price'));
            $('#addProduct').attr('data-price-all', price);
            $('#addProduct_price_text').html('додати за '+price.toFixed(2)+' грн');
            button = document.getElementById('addProduct');
            render();
        });

        $('.item__count .count_plus').on('click', function () {
            var input = $(this).parent().find('input');
            var inputVal = parseInt(input.val());

            if($(this).hasClass('borts_count_plus')) {
                var inputs = $(".borts_inputs");
                var count = 0;
                for (var i = 0; i < inputs.length; i++) {
                    count = count + $(inputs[i]).val();
                }
                if (count > 0) return;
            }

            if (inputVal == parseInt(input.attr('max'))) {
                return;
            } else {
                var n = inputVal + 1;
                input.val(n);
            }
            price = parseFloat(price) + parseFloat(input.attr('data-price'));
            $('#addProduct').attr('data-price-all', price);
            $('#addProduct_price_text').html('додати за '+price.toFixed(2)+' грн');
            button = document.getElementById('addProduct');
            render();
        });

        $('#addProduct').on('click', function () {
            if (!disabled) {
                disabled = true;
                tg.MainButton.showProgress(true);
                var inputs = $(".ingredients_inputs");
                var ingredients = '{"ingredients": [';
                var n = 0;
                var ins = '';
                for (var i = 0; i < inputs.length; i++) {
                    if ($(inputs[i]).val() > 0) {
                        n++;
                        if (n > 1) ins = ', ';
                        else ins = '';
                        ingredients = ingredients + ins + '{"id": "'+$(inputs[i]).attr('data-id')+'", "name": '+'"'+$(inputs[i]).attr('data-name')+'", '+'"quantity": "'+$(inputs[i]).val()+'", "category_id": "'+$(inputs[i]).attr('data-category-id')+'", "combination_id": "'+$(inputs[i]).attr('data-combination-id')+'", "price": "'+$(inputs[i]).attr('data-price')+'"}';
                    }
                }
                ingredients = ingredients + ']}';
                button.classList.add('loading');
                button.classList.remove('ready');
                $('#addProduct_price_text').html('');
                $.ajax({
                    type: "POST",
                    url: "{{ route('add_product_to_cart') }}",
                    data: "_token={{ csrf_token() }}&user_id="+`${tg.initDataUnsafe.user.id}`+"&product_id="+$('#addProduct').attr('data-product-id')+"&combination_id="+$('#addProduct').attr('data-combination-id')+"&price="+$('#addProduct').attr('data-price')+'&ingredients='+ingredients,
                    cache: false
                }).done(function(data) {
                    tg.MainButton.hideProgress();

                    // Working hours check
                    if (data && data['working_hours_closed']) {
                        button.classList.remove('loading');
                        button.classList.add('ready');
                        disabled = false;
                        var whMsg = data['message'] || 'Зараз неробочий час. Спробуйте пізніше.';
                        $('#addProduct_price_text').html('додати за '+$('#addProduct').attr('data-price')+' грн');
                        $("#modal_body").html(button_close+'<div style="padding: 30px 20px; text-align: center;"><div style="font-size: 48px; margin-bottom: 15px;">🕐</div><div style="font-size: 16px; color: #333; line-height: 1.5;">'+whMsg.replace(/\n/g, '<br>')+'</div></div>');
                        $("#modal_footer").html("");
                        $("#modalDialog").modal("show");
                        return;
                    }

                    var inputs = $(".ingredients_inputs");
                    for (var n = 0; n < inputs.length; n++) {
                        $(inputs[n]).val(0);
                    }
                    button.classList.add('complete');
                    button.classList.remove('loading');
                    // Completed stage
                    setTimeout(() => {
                        window.initBurst();
                        setTimeout(() => {
                            $('#addProduct').attr('data-price-all', $('#addProduct').attr('data-price'));
                            button.classList.remove('complete');
                            button.classList.add('ready');
                            setTimeout(() => {
                                $('#addProduct_price_text').html('додати за '+$('#addProduct').attr('data-price')+' грн');
                                price = $('#addProduct').attr('data-price');
                                disabled = false;
                            }, 400);
                        }, 2700);
                    }, 500);
                    if (data) {
                        if (data['products_sum'] > 0) {
                            tg.MainButton.text = "👀 Моє замовлення ("+data['products_sum']+" грн)";
                            tg.MainButton.show();
                        }
                        else {
                            tg.MainButton.hide();
                        }
                    }
                    else {
                        $("#modal_body").html(button_close+"Щось пішло не так...");
                    }
                }).fail(function() {
                    tg.MainButton.hideProgress();
                    button.classList.remove('loading');
                    button.classList.add('ready');
                    disabled = false;
                    $('#addProduct_price_text').html('додати за '+$('#addProduct').attr('data-price')+' грн');
                    $("#modal_body").html(button_close+"Щось пішло не так. Спробуйте ще раз.");
                    $("#modal_footer").html("");
                    $("#modalDialog").modal("show");
                });
            }

        });

    });

    // New btn
    var button = document.getElementById('addProduct');
    var disabled = false;
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    let cx = ctx.canvas.width / 2;
    let cy = ctx.canvas.height / 2;

    // add Confetti/Sequince objects to arrays to draw them
    let confetti = [];
    let sequins = [];

    // ammount to add on each button press
    const confettiCount = 20;
    const sequinCount = 10;

    // "physics" variables
    const gravityConfetti = 0.3;
    const gravitySequins = 0.55;
    const dragConfetti = 0.075;
    const dragSequins = 0.02;
    const terminalVelocity = 3;

    // colors, back side is darker for confetti flipping
    // const colors = [
    //     { front: '#7b5cff', back: '#6245e0' }, // Purple
    //     { front: '#b3c7ff', back: '#8fa5e5' }, // Light Blue
    //     { front: '#5c86ff', back: '#345dd1' }  // Darker Blue
    // ];

    const colors = [
        { front: '#ffe600', back: '#3000ff' }, // Purple
        { front: '#ffea8d', back: '#3a6bff' }, // Light Blue
        { front: '#dbe240', back: '#345dd1' }  // Darker Blue
    ];

    // draws the elements on the canvas
    render = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        confetti.forEach((confetto, index) => {
            let width = (confetto.dimensions.x * confetto.scale.x);
            let height = (confetto.dimensions.y * confetto.scale.y);

            // move canvas to position and rotate
            ctx.translate(confetto.position.x, confetto.position.y);
            ctx.rotate(confetto.rotation);

            // update confetto "physics" values
            confetto.update();

            // get front or back fill color
            ctx.fillStyle = confetto.scale.y > 0 ? confetto.color.front : confetto.color.back;

            // draw confetto
            ctx.fillRect(-width / 2, -height / 2, width, height);

            // reset transform matrix
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            // clear rectangle where button cuts off
            if (confetto.velocity.y < 0) {
                ctx.clearRect(canvas.width / 2 - button.offsetWidth / 2, canvas.height / 2 + button.offsetHeight / 2, button.offsetWidth, button.offsetHeight);
            }
        })

        sequins.forEach((sequin, index) => {
            // move canvas to position
            ctx.translate(sequin.position.x, sequin.position.y);

            // update sequin "physics" values
            sequin.update();

            // set the color
            ctx.fillStyle = sequin.color;

            // draw sequin
            ctx.beginPath();
            ctx.arc(0, 0, sequin.radius, 0, 2 * Math.PI);
            ctx.fill();

            // reset transform matrix
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            // clear rectangle where button cuts off
            if (sequin.velocity.y < 0) {
                ctx.clearRect(canvas.width / 2 - button.offsetWidth / 2, canvas.height / 2 + button.offsetHeight / 2, button.offsetWidth, button.offsetHeight);
            }
        })

        // remove confetti and sequins that fall off the screen
        // must be done in seperate loops to avoid noticeable flickering
        confetti.forEach((confetto, index) => {
            if (confetto.position.y >= canvas.height) confetti.splice(index, 1);
        });
        sequins.forEach((sequin, index) => {
            if (sequin.position.y >= canvas.height) sequins.splice(index, 1);
        });

        window.requestAnimationFrame(render);
    }

    $(document).ready( function() {

        // helper function to pick a random number within a range
        randomRange = (min, max) => Math.random() * (max - min) + min;

        // helper function to get initial velocities for confetti
        // this weighted spread helps the confetti look more realistic
        initConfettoVelocity = (xRange, yRange) => {
            const x = randomRange(xRange[0], xRange[1]);
            const range = yRange[1] - yRange[0] + 1;
            let y = yRange[1] - Math.abs(randomRange(0, range) + randomRange(0, range) - range);
            if (y >= yRange[1] - 1) {
                // Occasional confetto goes higher than the max
                y += (Math.random() < .25) ? randomRange(1, 3) : 0;
            }
            return { x: x, y: -y };
        }

        // Confetto Class
        function Confetto() {
            this.randomModifier = randomRange(0, 99);
            this.color = colors[Math.floor(randomRange(0, colors.length))];
            this.dimensions = {
                x: randomRange(5, 9),
                y: randomRange(8, 15),
            };
            this.position = {
                x: randomRange(canvas.width / 2 - button.offsetWidth / 4, canvas.width / 2 + button.offsetWidth / 4),
                y: randomRange(canvas.height / 2 + button.offsetHeight / 2 + 8, canvas.height / 2 + (1.5 * button.offsetHeight) - 8),
            };
            this.rotation = randomRange(0, 2 * Math.PI);
            this.scale = {
                x: 1,
                y: 1,
            };
            this.velocity = initConfettoVelocity([-9, 9], [6, 11]);
        }
        Confetto.prototype.update = function () {
            // apply forces to velocity
            this.velocity.x -= this.velocity.x * dragConfetti;
            this.velocity.y = Math.min(this.velocity.y + gravityConfetti, terminalVelocity);
            this.velocity.x += Math.random() > 0.5 ? Math.random() : -Math.random();

            // set position
            this.position.x += this.velocity.x;
            this.position.y += this.velocity.y;

            // spin confetto by scaling y and set the color, .09 just slows cosine frequency
            this.scale.y = Math.cos((this.position.y + this.randomModifier) * 0.09);
        }

        // Sequin Class
        function Sequin() {
            this.color = colors[Math.floor(randomRange(0, colors.length))].back,
                this.radius = randomRange(1, 2),
                this.position = {
                    x: randomRange(canvas.width / 2 - button.offsetWidth / 3, canvas.width / 2 + button.offsetWidth / 3),
                    y: randomRange(canvas.height / 2 + button.offsetHeight / 2 + 8, canvas.height / 2 + (1.5 * button.offsetHeight) - 8),
                },
                this.velocity = {
                    x: randomRange(-6, 6),
                    y: randomRange(-8, -12)
                }
        }
        Sequin.prototype.update = function () {
            // apply forces to velocity
            this.velocity.x -= this.velocity.x * dragSequins;
            this.velocity.y = this.velocity.y + gravitySequins;

            // set position
            this.position.x += this.velocity.x;
            this.position.y += this.velocity.y;
        }

        // add elements to arrays to be drawn
        initBurst = () => {
            for (let i = 0; i < confettiCount; i++) {
                confetti.push(new Confetto());
            }
            for (let i = 0; i < sequinCount; i++) {
                sequins.push(new Sequin());
            }
        }

        // // cycle through button states when clicked
        // clickButton = () => {
        //     if (!disabled) {
        //         disabled = true;
        //         // Loading stage
        //         button.classList.add('loading');
        //         button.classList.remove('ready');
        //         setTimeout(() => {
        //             // Completed stage
        //             button.classList.add('complete');
        //             button.classList.remove('loading');
        //             setTimeout(() => {
        //                 window.initBurst();
        //                 setTimeout(() => {
        //                     // Reset button so user can select it again
        //                     disabled = false;
        //                     button.classList.add('ready');
        //                     button.classList.remove('complete');
        //                 }, 1500);
        //             }, 320);
        //         }, 1500);
        //     }
        // }

        // re-init canvas if the window size changes
        resizeCanvas = () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            cx = ctx.canvas.width / 2;
            cy = ctx.canvas.height / 2;
        }

        // resize listenter
        window.addEventListener('resize', () => {
            resizeCanvas();
        });

        // // click button on spacebar or return keypress
        // document.body.onkeyup = (e) => {
        //    if (e.keyCode == 13 || e.keyCode == 32) {
        //       clickButton();
        //    }
        // }

        // Set up button text transition timings on page load
        textElements = button.querySelectorAll('.button-text');
        textElements.forEach((element) => {
            characters = element.innerText.split('');
            let characterHTML = '';
            characters.forEach((letter, index) => {
                characterHTML += `<span class="char${index}" style="--d:${index * 30}ms; --dr:${(characters.length - index - 1) * 30}ms;">${letter}</span>`;
            })
            element.innerHTML = characterHTML;
        })

        // kick off the render loop
        render();

    });

</script>

