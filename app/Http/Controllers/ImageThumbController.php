<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Telegram\BotMenuNewController;
use App\Models\BotMenu;
use App\Http\Controllers\Telegram\BotMenuController;
use App\Http\Controllers\Telegram\BotSettingsController;
use App\Http\Controllers\Telegram\MenuCommandController;
use App\Models\BotSettings;
use App\Models\PrestaShop_Category;
use App\Models\Simpla_Categories;
use App\Models\Simpla_Images;
use Illuminate\Http\Request as Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageThumbController extends Controller
{

    public static function execute(Request $request) {

        $input = $request->except('_token');

        $w = 100;
        $h = 100;

        $file = Simpla_Images::where('product_id', $input['product_id'])->where('position', 0)->first()['filename'];
        $filename = 'http://ecopizza.com.ua/files/originals/'.$file;
        $path = public_path().'/assets/img/thumb/';

//        $filename = 'http://ecopizza.com.ua/files/originals/'.$input['filename'];

        // определим коэффициент сжатия изображения, которое будем генерить
        $ratio = $w/$h;
        // получим размеры исходного изображения
        $size_img = getimagesize($filename);
        // Если размеры меньше, то масштабирования не нужно
        if (($size_img[0]<$w) && ($size_img[1]<$h)) return true;
        // получим коэффициент сжатия исходного изображения
        $src_ratio=$size_img[0]/$size_img[1];

        // Здесь вычисляем размеры уменьшенной копии,
        // чтобы при масштабировании сохранились
        // пропорции исходного изображения
        if ($ratio<$src_ratio)
        {
            $h = $w/$src_ratio;
        }
        else
        {
            $w = $h*$src_ratio;
        }
        // создадим пустое изображение по заданным размерам
        $dest_img = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($dest_img, 255, 255, 255);

        //Отключаем режим сопряжения цветов
        imagealphablending($dest_img, false);
        //Включаем сохранение альфа канала
        imagesavealpha($dest_img, true);

        if ($size_img[2]==2)  $src_img = imagecreatefromjpeg($filename);
        else if ($size_img[2]==1) $src_img = imagecreatefromgif($filename);
        else if ($size_img[2]==3) $src_img = imagecreatefrompng($filename);

        // масштабируем изображение     функцией imagecopyresampled()
        // $dest_img - уменьшенная копия
        // $src_img - исходной изображение
        // $w - ширина уменьшенной копии
        // $h - высота уменьшенной копии
        // $size_img[0] - ширина исходного изображения
        // $size_img[1] - высота исходного изображения
        imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $w, $h, $size_img[0], $size_img[1]);

        // Выводим уменьшенную копию в поток вывода
        if ($size_img[2]==2)  header('Content-type: image/jpg');
        else if ($size_img[2]==1) header('Content-type: image/gif');
        else if ($size_img[2]==3) header('Content-type: image/png');
        // Выводим уменьшенную копию в поток вывода
        if ($size_img[2]==2)  imagejpeg($dest_img, $path.$input['product_id'].'.jpg');
        else if ($size_img[2]==1) imagegif($dest_img, $path.$input['product_id'].'.gif');
        else if ($size_img[2]==3) imagepng($dest_img, $path.$input['product_id'].'.png');

        // чистим память от созданных изображений
        imagedestroy($dest_img);
        imagedestroy($src_img);
        return true;

    }

    public static function resample($product, $abra_kadabra) {

        Log::info('Начал в '.date("H:i:s").'; '.$product->name.'; '.$product->filename);
        $time_start = time();
        $w = 100;
        $h = 100;

        $filename = 'http://ecopizza.com.ua/files/originals/'.$product->filename;
        $ext = substr(strrchr($product->filename, '.'), 1);
        $path = public_path().'/assets/img/thumb/';

//        $filename = 'http://ecopizza.com.ua/files/originals/'.$input['filename'];

        // определим коэффициент сжатия изображения, которое будем генерить
        $ratio = $w/$h;
        // получим размеры исходного изображения
        $size_img = getimagesize($filename);
        // Если размеры меньше, то масштабирования не нужно
        if (($size_img[0]<$w) && ($size_img[1]<$h)) return true;
        // получим коэффициент сжатия исходного изображения
        $src_ratio=$size_img[0]/$size_img[1];

        // Здесь вычисляем размеры уменьшенной копии,
        // чтобы при масштабировании сохранились
        // пропорции исходного изображения
        if ($ratio<$src_ratio)
        {
            $h = $w/$src_ratio;
        }
        else
        {
            $w = $h*$src_ratio;
        }
        // создадим пустое изображение по заданным размерам
        $dest_img = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($dest_img, 255, 255, 255);

        //Отключаем режим сопряжения цветов
        imagealphablending($dest_img, false);
        //Включаем сохранение альфа канала
        imagesavealpha($dest_img, true);

        if ($size_img[2]==2)  $src_img = imagecreatefromjpeg($filename);
        else if ($size_img[2]==1) $src_img = imagecreatefromgif($filename);
        else if ($size_img[2]==3) $src_img = imagecreatefrompng($filename);

        // масштабируем изображение     функцией imagecopyresampled()
        // $dest_img - уменьшенная копия
        // $src_img - исходной изображение
        // $w - ширина уменьшенной копии
        // $h - высота уменьшенной копии
        // $size_img[0] - ширина исходного изображения
        // $size_img[1] - высота исходного изображения
        imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $w, $h, $size_img[0], $size_img[1]);

        // Выводим уменьшенную копию в поток вывода
//        if ($size_img[2]==2)  header('Content-type: image/jpg');
//        else if ($size_img[2]==1) header('Content-type: image/gif');
//        else if ($size_img[2]==3) header('Content-type: image/png');

        // Выводим уменьшенную копию в поток вывода
        if ($size_img[2]==2)  imagejpeg($dest_img, $path.$abra_kadabra.$product->id.'.'.$ext);
        else if ($size_img[2]==1) imagegif($dest_img, $path.$abra_kadabra.$product->id.'.'.$ext);
        else if ($size_img[2]==3) imagepng($dest_img, $path.$abra_kadabra.$product->id.'.'.$ext);

        // чистим память от созданных изображений
        imagedestroy($dest_img);
        imagedestroy($src_img);

        $time_end = time();
        $time_spent = $time_end - $time_start;
//        echo 'Выполнил за '.$time_spent.'; '.$product->name.'; '.$path.$product->id.'<br />';
        Log::info('Закончил в '.date("H:i:s"));
        Log::info('Выполнил за '.$time_spent.' сек; '.$product->name.'; '.$path.$product->id);
        Log::info('------------------------------------------------------------------------------------');

        return true;

    }

    public static function deleteFile($product, $abra_kadabra)
    {
//        $ext = substr(strrchr($product->filename, '.'), 1);
//        $filename = $abra_kadabra.$product->id.'.'.$ext;
        $filename = $abra_kadabra.$product->id.'.webp';
        $delete_file = Storage::disk('public_folder_images_thumbs')->delete($filename);
//        if ($delete_file) echo 'Удалил файл '.$filename.'<br />';
        return $delete_file;
    }

    public static function generateAbraKadabra()
    {
        $max = 10;
        $comb = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $abrakadabra = [];
        $combLen = strlen($comb) - 1;
        for ($i = 0; $i < $max; $i++) {
            $n = rand(0, $combLen);
            $abrakadabra[] = $comb[$n];
        }
        $result = date("Y_m_d").'_'.implode($abrakadabra).'__';
        return $result;
    }

    public static function all()
    {
        $abra_kadabra = BotSettingsController::getSettings(null, 'abra_kadabra_for_thumbs_all')['settings_value'];
        $abra_kadabra_new = self::generateAbraKadabra();

        $category_select = isset($input['category_select']) && $input['category_select'] !== null ? $input['category_select'] : 3;

        try {
            $webService = new PrestaShopWebserviceController('https://ecopizza.com.ua', env('PRESTASHOP_KEY'));
//            dd($webService, env('PRESTASHOP_KEY'));

            $product_features = $webService->get(array('url' => 'https://ecopizza.com.ua/api/product_feature_values?display=full&language='.BotMenuNewController::$language), true);
            $product_features = json_decode($product_features);
            $product_features = collect($product_features->product_feature_values);

            $product_options = $webService->get(array('url' => 'https://ecopizza.com.ua/api/product_option_values?display=full&language='.BotMenuNewController::$language), true);
            $product_options = json_decode($product_options);
            $product_options = collect($product_options->product_option_values);

            $product_combinations = $webService->get(array('url' => 'https://ecopizza.com.ua/api/combinations?display=full&language='.BotMenuNewController::$language), true);
            $product_combinations = json_decode($product_combinations);
            $product_combinations = collect($product_combinations->combinations);

            $categories = $webService->get(array('url' => 'https://ecopizza.com.ua/api/categories?display=full&filter[active]=[1]&language='.BotMenuNewController::$language), true);
            $categories = json_decode($categories);
            $categories = collect($categories->categories);
//            dd($categories);
//            $category = $categories->where('id', $category_select)->first();

            foreach ($categories as $category) {
                $associations = isset($category->associations) ? $category->associations : null;
                $products = collect();
                if ($associations !== null) {
//                    Log::info('associations not null');
                    if (isset($associations->categories)) {
//                        Log::info($associations->categories);
                        foreach ($associations->categories as $item) {
                            $item_category = $categories->where('id', $item->id)->first();
                            $item_associations = isset($item_category->associations) ? $item_category->associations : null;
                            if ($item_associations !== null) {
                                if (isset($item_associations->products)) {
                                    $products = BotMenuNewController::manipulationWithProducts($category_select, $products, $item_associations->products, $product_features, $product_combinations, $product_options);
                                }
                            }
                        }
                    }
                    if (isset($associations->products)) {
                        $products = BotMenuNewController::manipulationWithProducts($category_select, $products, $associations->products, $product_features, $product_combinations, $product_options);            }
                }
                foreach ($products as $product)
                {
                    if (isset($product->id_default_image) && $product->id_default_image > 0) {
                        try {
                            $content = @file_get_contents("https://ecopizza.com.ua/api/images/products/".$product->id."/".$product->id_default_image."/cart_default?ws_key=".env('PRESTASHOP_KEY')."&output_format=JSON&display=full");
                            if ($content) {
                                file_put_contents(public_path().'/assets/img/thumb/'.$abra_kadabra_new.$product->id.'.webp', $content);
                            }
                        } catch (\Exception $e) {
                            Log::warning('Thumb download failed for product '.$product->id.': '.$e->getMessage());
                        }
                        self::deleteFile($product, $abra_kadabra);
                    }
                }
            }
        } catch (PrestaShopWebserviceExceptionController $e) {
            Log::warning('Error!!!');
            Log::warning($e);
            $data = [
                'category_select' => $category_select,
                'products' => null,
                'message' => $e,
            ];
            return $data;
        }

//        dd($products);

        // фото продуктов
//        foreach ($products as $product)
//        {
//            if (isset($product->id_default_image) && $product->id_default_image > 0) {
//                $content = file_get_contents("https://stage.ecopizza.com.ua/api/images/products/".$product->id."/".$product->id_default_image."/cart_default?ws_key=NXUAQSSLS82SFTVBMXCBMCFIFRPVWJZR&output_format=JSON&display=full");
//                if ($content) $save_file = file_put_contents(public_path().'/assets/img/thumb/'.$abra_kadabra_new.$product->id.'.webp', $content);
//            }
//        }

//        $menus = BotMenu::where('enabled', 1)->orderBy('menu_sort', 'asc')->get();
//        foreach ($menus as $menu) {
//            $cat_id = Simpla_Categories::where('url', $menu->menu_key)->first()['id'];
//            $products = Simpla_Categories::join('s_products_categories', 's_categories.id', 's_products_categories.category_id')
//                ->join('s_products', function ($join) {
//                    $join->on('s_products.id', 's_products_categories.product_id')
//                        ->where('s_products.visible', 1);
//                })
//                ->leftJoin('s_tabs', 's_products_categories.product_id', 's_tabs.product_id')
//                ->join('s_images', 's_images.product_id', 's_products.id')
//                ->where(function ($query) use ($cat_id) {
//                    $query->where('s_categories.id', $cat_id)
//                        ->orWhere('s_categories.parent_id', $cat_id);
//                })
//                ->where('s_images.position', 0)
//                ->groupBy('s_products.id')
//                ->orderBy('s_products.position', 'desc')
//                ->get(['s_products.id', 's_categories.id as cat_id', 's_products.name as name', 's_products.featured', 's_products.position', 's_tabs.body as description', 's_images.filename']);
//
//            foreach ($products as $product) {
//                self::deleteFile($product, $abra_kadabra);
//                self::resample($product, $abra_kadabra_new);
//            }
//        }
        $update_abrakadabra = BotSettings::where('settings_name', 'abra_kadabra_for_thumbs_all')->update(['settings_value' => $abra_kadabra_new]);
        return 'Картинки обновлены';
    }

}
