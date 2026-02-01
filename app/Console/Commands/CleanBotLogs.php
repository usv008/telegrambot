<?php

namespace App\Console\Commands;

use App\Models\BotSendingMessagesHistory;
use App\Models\BotUserHistory;
use Illuminate\Console\Command;

class CleanBotLogs extends Command
{
    protected $signature = 'bot:clean-logs {--days=60 : Number of days to keep}';

    protected $description = 'Delete old records from bot_user_history_new and bot_sending_messages_history tables';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $deletedHistory = BotUserHistory::where('date_z', '<', $cutoff)->delete();
        $this->info("Deleted {$deletedHistory} records from bot_user_history_new (older than {$days} days).");

        $deletedSending = BotSendingMessagesHistory::where('date_z', '<', $cutoff)->delete();
        $this->info("Deleted {$deletedSending} records from bot_sending_messages_history (older than {$days} days).");

        return 0;
    }
}
