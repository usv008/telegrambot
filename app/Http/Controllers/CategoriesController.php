<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Validator;


class CategoriesController extends Controller
{

    public function execute(Request $request) {

        if (($request->isMethod('post')) || ($request->isMethod('get'))) {

            $input = $request->except('_token');

            $status = 'Категория добавлена!';

            $data = [
                'title' => 'Категории',
                'cat_prod' => 'categories',
            ];

            return redirect('admin/catalog/categories')->with('status', $status);
            dd($input);
            return view('admin.catalog', $data);

            $title = 'ModalDialog';
            $view = 'admin.modaldialog';
            $message = '';

            if ($input['action'] == 'categories_add') {

                $title = 'Добавление категории';
                $view = 'admin.showmodaldialog_category_add';

            }

            $data = [
                'title' => $title,
                'data' => $input,
                'message' => $message,
            ];

            if (view()->exists($view)) {
                return view($view, $data);
            }

        }

    }

}
