<?php


use danog\MadelineProto\API;
use danog\MadelineProto\messages;

class kTelegramSaver
{
    private $phone = null;
    private $tagDelimiter = null;
    private $savePath = null;
    private $madeline = null;

    private $sessionName = "kTelegramSaverByTags.sess";
    private $api_id = 1060318;
    private $api_hash = "9f139864793b82b7609edd8344832cef";


    /**
     * kTelegramSaver constructor.
     *
     * @param $telephone
     * @param string $savePath
     * @param string $tagDelimiter
     * @version 1.0.0
     * @author kFerst <kferst@icloud.com>
     */
    public function __construct($telephone, $savePath = __DIR__ . '/files', $tagDelimiter = '#')
    {
        $this->phone = $telephone;
        $this->tagDelimiter = $tagDelimiter;
        $this->savePath = $savePath;
        if (!file_exists($this->savePath)) {
            mkdir($this->savePath);
        }
        /*
         * Download lib
         * https://github.com/danog/MadelineProto
         * */
        if (!file_exists('madeline.php')) {
            copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
        }
        include 'madeline.php';
        /*
         * Check session file
         * */
        if (file_exists($this->sessionName)) {
            $madeline = new API($this->sessionName);
            $this->madeline = $madeline;
        } else {
            /*
             * Create session file
             * */
            $madeline = new API([
                'app_info' => [
                    'api_id' => $this->api_id,
                    'api_hash' => $this->api_hash,
                ]
            ]);

            /*
             * Set session name
             * */
            $madeline->session = $this->sessionName;

            /*
             * Save session
             * */
            $madeline->serialize();

            /*
             * Auth by phone
             * */
            $madeline->phone_login($this->phone);
            /*
             * Auth code
             * */
            $code = readline('Enter the code you received: ');
            $madeline->complete_phone_login($code);

            $this->madeline = $madeline;
        }
    }

    /**
     * Return a list of all chats that have a user
     *
     * @return array
     */
    public function getAllChats()
    {
        $chats = $this->madeline->messages->getAllChats([]);
        $chats_ = [];
        foreach ($chats['chats'] as $chat) {
            $chats_[] = [
                'type' => $chat['_'],
                'title' => $chat['title'],
                'id' => $chat['id']
            ];
        }
        return $chats_;
    }

    /**
     * Return messages history for current chat
     *
     * @param $chat_id
     * @param int $messages_limit
     * @param int $message_offset
     * @param int $max_id
     * @param int $min_id
     * @return messages
     */
    public function getChatHistory($chat_id, $messages_limit = 100, $message_offset = 0, $max_id = 0, $min_id = 0)
    {
        $messages = $this->madeline->messages->getHistory([
            'peer' => "channel#$chat_id",
            'offset_id' => $message_offset,
            'offset_date' => 0,
            'add_offset' => 0,
            'limit' => $messages_limit,
            'max_id' => $max_id,
            'min_id' => $min_id,
        ]);
        return $messages;
    }

    /**
     * Returns an array of messages sorted by tags
     *
     * <code>
     * $filter = [
     *      'tagsForSave' => [],
     *      'tagsForIgnore' => [],
     *      'removeText' => []
     * ]
     *
     * $return = [
     *      'count'=> int,
     *      'items' => []
     * ]
     * </code>
     *
     * @param array $messages
     * @param array $filter (See above)
     * @return array array (See above)
     */
    public function getFilteredMessage($messages, $filter = ['tagsForSave' => [], 'tagsForIgnore' => [], 'removeText' => []])
    {
        $filtered = [
            'count' => 0,
            'items' => []
        ];
        /*$messages['messages']*/
        foreach ($messages as $message) {
            $message['message'] = str_replace($filter['removeText'], '', str_replace(' ', '', $message['message']));
            $tags = explode($this->tagDelimiter, $message['message']);
            foreach ($tags as $tag_id => $tag) {
                $tags[$tag_id] = trim($tag);
                if (strlen(trim($tag)) > 12 || strlen(trim($tag)) === 0) unset($tags[$tag_id]);
            }

            $tags = array_values($tags);
            $filter_target = false;

            foreach ($filter['tagsForSave'] as $tagSave) {
                if (in_array(trim($tagSave), $tags)) $filter_target = true;
            }

            foreach ($filter['tagsForIgnore'] as $tagIgnore) {
                if (in_array(trim($tagIgnore), $tags)) $filter_target = false;
            }

            if ($filter_target) {
                if (isset($message['media'])) {
                    $filtered['items'][] = [
                        'message_id' => $message['id'],
                        'text' => $message['message'],
                        'tags_list' => $tags,
                        'message_media' => $message['media']
                    ];
                }
            }

        }
        $filtered['count'] = count($filtered['items']);
        return $filtered;
    }

    /**
     * Save images
     *
     *
     *<code>
     * Example:
     * $save_list = $kTelegramSaver->saveFilteredMessages($messages['items'])
     * </code>
     *
     * @param array $filtered_messages
     * @return array
     * @throws Exception
     */
    public function saveFilteredMessages($filtered_messages = [])
    {
        $save_list = [];
        foreach ($filtered_messages as $message) {
            $saved = $this->madeline->downloadToDir($message['message_media'], $this->savePath);
            $name = md5(random_bytes(20)) . '.jpg';
            rename($saved, $this->savePath . '/' . $name);
            $save_list[] = $this->savePath . '/' . $name;
        }
        return $save_list;
    }
}