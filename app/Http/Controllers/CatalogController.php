<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as LRequest;


class CatalogController extends Controller
{

    public static function show_categories() {

        if (view()->exists('admin.catalog')) {

            $data = [
                'title' => 'Категории',
                'cat_prod' => 'categories',
            ];

            return view('admin.catalog', $data);

        }

    }

    public static function show_products() {

        if (view()->exists('admin.catalog')) {

            $data = [
                'title' => 'Товары',
                'cat_prod' => 'products',
            ];

            return view('admin.catalog', $data);

        }

    }

    public static function show_sizes() {

        if (view()->exists('admin.catalog')) {

            $data = [
                'title' => 'Размеры',
                'cat_prod' => 'sizes',
            ];

            return view('admin.catalog', $data);

        }

    }

}
