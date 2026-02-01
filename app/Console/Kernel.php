<?php

namespace App\Console;

use App\Http\Controllers\BotSeaBattleCronController;
use App\Http\Controllers\CashBackController;
use App\Http\Controllers\Telegram\BotCartController;
use App\Http\Controllers\Telegram\BotCashbackController;
use App\Http\Controllers\Telegram\BotFeedBackController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
//        $schedule->call(function () {
//            Log::info('cron: '.date("H:i:s"));
//        })->everyMinute();

        $schedule->call(function (PhpTelegramBotContract $telegram_bot) {
            $seaBattleCron = BotSeaBattleCronController::execute();
        })->everyMinute();

        /**
         * отправляем оценки
         */
        $schedule->call(function (PhpTelegramBotContract $telegram_bot) {
            BotFeedBackController::sendFeedBack();
        })->dailyAt('12:00');

        /**
         * начисляем КБ если заказ выполнен и/или возвращаем, если заказ отменен
         */
//        $schedule->call(function (PhpTelegramBotContract $telegram_bot) {
//    	    CashBackController::addCashback();
//            CashBackController::returnCashback();
//            BotCartController::checkUsersCartForRiminder();
//        })->cron('*/15 * * * *');

        /**
         * Очищаем корзины всех пользователей!
         * За исключением выигрышных пицц и подарочных продуктов.
         */
        $schedule->call(function (PhpTelegramBotContract $telegram_bot) {
            BotCartController::clear_all_cart_without_action_pizza_and_product_present();
            $delete_cart_rules = BotCashbackController::deleteCartRulesByCron();
        })->dailyAt('22:30');

        /**
         * удаляем правила корзины для КБ, начисляем КБ если заказ выполнен и/или возвращаем, если заказ отменен
         */
        $schedule->call(function (PhpTelegramBotContract $telegram_bot) {
            $cashback_add = CashBackController::addCashbackNew();
            $cashback_return = CashBackController::returnCashbackNew();
        })->cron('*/15 * * * *');

        /**
         * Очищаем старые логи (старше 60 дней)
         */
        $schedule->command('bot:clean-logs')->dailyAt('03:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
