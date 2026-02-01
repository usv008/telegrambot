<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\InlineQuery\InlineQueryResultArticle;
use Longman\TelegramBot\Entities\InlineQuery\InlineQueryResultPhoto;
use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

use App\Http\Controllers\Telegram\InlineQueryController;

use Session;

/**
 * Inline query command
 *
 * Command that handles inline queries.
 */
class InlinequeryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'inlinequery';
    /**
     * @var string
     */
    protected $description = 'Reply to inline query';
    /**
     * @var string
     */
    protected $version = '1.1.1';
    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute(): ServerResponse
    {

        $inline_query = $this->getInlineQuery();
        $query = $inline_query->getQuery();
        $user_id = $inline_query->getFrom()->getId();
        $offset = $inline_query->getOffset();

        $next_offset = $offset !== '' ? $offset : 0;
        $take = 48;

        $data    = ['inline_query_id' => $inline_query->getId()];
        $results = [];

        if ($query !== '') {

            list($next_offset, $articles) = InlineQueryController::search($user_id, $next_offset, $take, $query);
            // $articles = InlineQueryController::get_resto_all();
            foreach ($articles as $article) {
                $results[] = new InlineQueryResultArticle($article);
            }

        }
        else {

            list($next_offset, $articles) = InlineQueryController::get_menu($user_id, $next_offset, $take);

            foreach ($articles as $article) {
                $results[] = new InlineQueryResultArticle($article);
                // $results[] = new InlineQueryResultPhoto($article);
            }

        }
        $data['results'] = '[' . implode(',', $results) . ']';
        $data['cache_time'] = 0;
        $data['next_offset'] = $next_offset;
//        $data['switch_pm_text'] = 'test text';
//        $data['switch_pm_parameter'] = 'testtestets';
        $answer = Request::answerInlineQuery($data);

        // $data_t = ['chat_id' => $user_id];
        // $data_t['text'] = 'inlinequery: '.$answer;
        // $send_t = Request::sendMessage($data_t);

        return $answer;

    }

}
