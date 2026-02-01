<header id="header_wrapper" class="p-0 m-0 pt-5 mt-4">
    @yield('header')

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($errors->any())
        {{ dd($errors) }}
        <div class="alert alert-danger">
            <ul><li>{{$errors->first()}}</li></ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
</header>
