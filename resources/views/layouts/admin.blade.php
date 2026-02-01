<!doctype html>
<html lang="en">
  <head>
      <meta charset="utf-8">
{{--      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">--}}
      <meta name="description" content="">
      <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
      <meta name="generator" content="Jekyll v3.8.5">

      <meta name="viewport" content="initial-scale=1.0, width=device-width" />

      <title>TelegramBot Admin</title>

      <!-- Bootstrap core CSS -->
      <link rel="stylesheet" href="{{ asset ('assets/css/jquery-ui.css') }}">
      <script src="{{ asset ('assets/js/jquery-1.12.4.js') }}"></script>
      <script src="{{ asset ('assets/js/jquery-ui.js') }}"></script>

      <script src="{{ asset ('assets/js/popper.min.js') }}" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
      <script src="{{ asset ('assets/js/bootstrap.min.js') }}" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

      <link rel="stylesheet" type="text/css" href="{{ asset ('assets/css/bootstrap.css') }}">
      <link rel="stylesheet" type="text/css" href="{{ asset ('assets/css/dataTables.bootstrap4.min.css') }}">
      <script type="text/javascript" charset="utf8" src="{{ asset ('assets/js/jquery.dataTables.js') }}"></script>
      <script type="text/javascript" charset="utf8" src="{{ asset ('assets/js/dataTables.bootstrap4.min.js') }}"></script>

      <script type="text/javascript" charset="utf8" src="{{ asset ('assets/js/ekko-lightbox.min.js') }}"></script>

      <script src="{{ asset ('assets/js/mapsjs-core.js') }}" type="text/javascript" charset="utf-8"></script>
      <script src="{{ asset ('assets/js/mapsjs-service.js') }}" type="text/javascript" charset="utf-8"></script>
      <script src="{{ asset ('assets/js/mapsjs-ui.js') }}" type="text/javascript" charset="utf-8"></script>
      <script src="{{ asset ('assets/js/mapsjs-core-legacy.js') }}" type="text/javascript" charset="utf-8"></script>
      <script src="{{ asset ('assets/js/mapsjs-clustering.js') }}" type="text/javascript" charset="utf-8"></script>
      <script type="text/javascript" src="{{ asset ('assets/js/mapsjs-mapevents.js') }}"></script>
      <link rel="stylesheet" type="text/css" href="{{ asset ('assets/css/mapsjs-ui.css') }}" />

      <script src="{{ asset ('assets/js/chart.js') }}"></script>
      <script type="text/javascript" src="{{ asset ('assets/js/bootstrap-filestyle.js') }}"></script>
      <script src="{{ asset ('assets/js/Chart.min.js') }}"></script>

      <link rel="stylesheet" type="text/css" href="{{ asset ('assets/css/chat.css') }}">

      <style>
          body {
              font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
          }

          #legend {
              font-family: Arial, sans-serif;
              background: #fff;
              padding: 10px;
              margin: 10px;
              border: 1px solid #222;
          }
          #legend h3 {
              margin-top: 0;
          }
          #legend img {
              vertical-align: middle;
          }
      </style>

      <style>
          /* The sidebar menu */
          .sidenav {
              height: 100%; /* Full-height: remove this if you want "auto" height */
              width: 200px; /* Set the width of the sidebar */
              position: fixed; /* Fixed Sidebar (stay in place on scroll) */
              z-index: 1; /* Stay on top */
              top: 0; /* Stay at the top */
              left: 0;
              /*background-color: #111; !* Black *!*/
              overflow-x: hidden; /* Disable horizontal scroll */
              padding-top: 60px;
          }

          /* The navigation menu links */
          .sidenav .catalog {
              /*padding: 6px 8px 6px 16px;*/
              padding: 2px 8px 0px 18px;
              text-decoration: none;
              font-size: 20px;
              color: #bdbdbd;
              display: block;
          }

          /* When you mouse over the navigation links, change their color */
          .sidenav p {
              padding: 0px 8px 0px 30px;
              margin: 0;
              text-decoration: none;
              font-size: 18px;
              color: #818181;
              display: block;
          }

          .sidenav p:hover {
              color: #ffffff;
          }

          /* The navigation menu links */
          .sidenav a {
              /*padding: 6px 8px 6px 16px;*/
              padding: 0px 8px 0px 30px;
              text-decoration: none;
              font-size: 16px;
              color: #818181;
              display: block;
          }

          /* When you mouse over the navigation links, change their color */
          .sidenav a:hover {
              color: #ffffff;
          }

          /* Style page content */
          .main {
              margin-left: 260px; /* Same as the width of the sidebar */
              padding: 0px 10px;
          }

          /* On smaller screens, where height is less than 450px, change the style of the sidebar (less padding and a smaller font size) */
          @media screen and (max-height: 450px) {
              .sidenav {padding-top: 15px;}
              .sidenav a {font-size: 18px;}
          }

          .data-table td {
              font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
              font-size: 14px;
              vertical-align: middle;
          }

          .data-table th {
              font-size: 15px;
          }

          .miniTableOrders {
              font-size: 14px;
          }

          .miniTableOrder {
              font-size: 14px;
          }

          .orders_content_left_th {
              font-weight: bold;
          }

          .orders_content_center_th {
              font-weight: bold;
              text-align: center;
          }

          .orders_content_center {
              text-align: center;
          }

          .orders_content_right_th {
              font-weight: bold;
              text-align: right;
          }

          .divTable{
              display: table;
              width: 100%;
          }
          .divTableRow {
              display: table-row;
          }
          .divTableHeading {
              background-color: #EEE;
              display: table-header-group;
          }
          .divTableCell, .divTableHead {
              border: 1px solid #999999;
              display: table-cell;
              padding: 3px 10px;
              text-align: center;
              /*margin: 8px;*/
              border-radius: 4px;
          }
          .divTableCellLeft, .divTableHeadLeft {
              border: 1px solid #999999;
              display: table-cell;
              padding: 3px 10px;
              /*margin: 8px;*/
              border-radius: 4px;
          }
          .divTableCellNo, .divTableHeadNo {
              border: 0px solid #999999;
              display: table-cell;
              padding: 3px 10px;
              /*margin: 8px;*/
              border-radius: 4px;
          }
          .divTableCellNoCenter, .divTableHeadNo {
              border: 0px solid #999999;
              display: table-cell;
              padding: 3px 10px;
              text-align: center;
              /*margin: 8px;*/
              border-radius: 4px;
          }
          .divTableHeading {
              background-color: #EEE;
              display: table-header-group;
              font-weight: bold;
          }
          .divTableFoot {
              background-color: #EEE;
              display: table-footer-group;
              font-weight: bold;
          }
          .divTableBody {
              display: table-row-group;
          }

      </style>

  </head>

  <body style="width: 100%;">
  <nav class="navbar navbar-expand-md navbar-default bg-success fixed-top">
        <a class="navbar-brand text-white" href="/admin">TelegramBot Admin</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <ul class="navbar-nav mr-auto">
                <!-- <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
                </li> -->
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link text-white" href="{{ route('archi') }}">ArchiDelivery</a>--}}
{{--                </li>--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link text-white" href="{{ route('map') }}">Карта</a>--}}
{{--                </li>--}}
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('catalog') }}">Каталог</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('users') }}">Бот</a>
                </li>

                <!-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Заведения</a>
                    <div class="dropdown-menu" aria-labelledby="dropdown01">
                        <a class="dropdown-item" href="#">Просмотр</a>
                        <a class="dropdown-item" href="#">Добавить</a>
                        <a class="dropdown-item" href="#">Редактировать</a>
                    </div>
                </li> -->
            </ul>
            <ul class="navbar-nav mt-2 mt-md-0">
                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        {{ Auth::user()->name }} <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();">
                            {{ __('Выйти') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

{{--    <header id="header_wrapper" class="p-0 m-0">--}}
{{--        @yield('header')--}}

{{--        @if (count($errors) > 0)--}}
{{--        <div class="alert alert-danger">--}}
{{--            <ul>--}}
{{--                @foreach ($errors->all() as $error)--}}
{{--                    <li>{{ $error }}</li>--}}
{{--                @endforeach--}}
{{--            </ul>--}}
{{--        </div>--}}
{{--        @endif--}}

{{--        @if (session('status'))--}}
{{--            <div class="alert alert-success">--}}
{{--                {{ session('status') }}--}}
{{--            </div>--}}
{{--        @endif--}}
{{--    </header>--}}

    <main role="main" class="w-100 p-0 m-0">

        <div class="w-100 p-0 m-0" style="position: relative;">
            @yield('content')
{{--            @include('admin.content')--}}
            <!-- <h1>Bootstrap starter template</h1>
            <p class="lead">Use this document as a way to quickly start any new project.<br> All you get is this text and a mostly barebones HTML document.</p> -->
        </div>

    </main><!-- /.container -->
</body>
</html>
