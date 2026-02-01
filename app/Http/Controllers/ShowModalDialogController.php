<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Validator;


class ShowModalDialogController extends Controller
{

    public function execute(Request $request) {

        if (($request->isMethod('post')) || ($request->isMethod('get'))) {

            $input = $request->except('_token');
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
