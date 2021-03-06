<?php 

namespace App\Repositories;


use Illuminate\Support\Facades\Storage;
use SwooleTW\Http\Table\Facades\SwooleTable as Table;
use SwooleTW\Http\Websocket\Facades\Websocket;
use Illuminate\Support\Facades\Validator;

class WebSocketRepository {

    const TABLE_NAME = 'onlineUsers';

    /**
     * Add Current User To Swoole Online Users Table
     *
     * @param $user
     * @param $fd
     * @return mixed
     */
    public function addToUsersTable($user, $fd)
    {
        return Table::get(self::TABLE_NAME)->set($user->user_id, [
                'fd'        => $fd,
                'role_id'   => $user->role_id,
        ]);
    }

    /**
     * Remove Current User From Swoole Online Users Table
     *
     * @param $userId
     * @return null
     */
    public function removeFromUsersTable($userId)
    {
        return Table::get(self::TABLE_NAME)->del($userId);
    }

    /**
     * Get All Online Users From Swoole Table
     *
     * @return array
     */
    public function getOnlineUsers()
    {
        $onlineUsers = [];
        $table = Table::get(self::TABLE_NAME);
        foreach ($table as $userId => $userData) {
            $onlineUsers[] = (int)$userId;
        }
        return $onlineUsers;
    }

    public function getUserIdFromFd($fd)
    {
        $table = Table::get(self::TABLE_NAME);
        foreach ($table as $userId => $userData) {
            if ((string)$userData['fd'] === (string)$fd) {
                return $userId;
            }
        }
    }

    public function getFdFromUserId($user_id)
    {
        $table = Table::get(self::TABLE_NAME);
        return $table->get($user_id, 'fd');
    }

    /**
     * Store New Message and Emit to Receivers
     * - get sender id from current websocket
     * - get receiver id from data or group id
     * - receiver id if private message
     * - group id if group chat
     * - check if message contain images
     * - store images as message
     * @param $websocket
     * @param $data
     * @param string $type [text||file]
     */
    public function onMessage($websocket, $data, $type='text')
    {
        $validationDataMethod = sprintf('validationMessage%s', ucfirst($type));
        $prepareDataMethod = sprintf('prepareMessage%s', ucfirst($type));
        $emitMethod = sprintf('emitMessage%s', ucfirst($type));

        if (!$this->{$validationDataMethod}($data)) return false;

        $messageData = $this->{$prepareDataMethod}($data);

        $this->{$emitMethod}($messageData);
    }

    /**
     * When Message Text
     *
     * @param $message
     */
    private function emitMessageText($message)
    {
        $message = $this->storeAndGetMessage($message);

        $this->emitMessage($message);
    }

    /**
     * When Message Files
     *
     * @param $messages
     */
    private function emitMessageFile($messages)
    {
        foreach ($messages as $message) {
            $this->emitMessageText($message);
        }
    }

    /**
     * Emit New Message To Target User || Target Group
     *
     * @param $message
     * @return bool|void
     */
    private function emitMessage($message)
    {
        if ($message->fk_receiver_id) {
            //private message
            return Websocket::toUserId([Websocket::getUserId(), $message->fk_receiver_id])->emit('private_message', $message);
        }
        if ($message->fk_group_id) {
            //group message
            return Websocket::to((string)$message->fk_group_id)->emit('private_message', $message);
        }
        return;
    }

    /**
     * Add Message To DB And Retrieve it
     *
     * @param $message
     * @return mixed
     */
    private function storeAndGetMessage($message)
    {
        $messageId = app(\App\Repositories\ChatRepository::class)->addMessage($message);

        return app(\App\Repositories\ChatRepository::class)->getMessage($messageId, !empty($message));
    }

    /**
     * Validation Message Text
     *
     * @param $data
     * @return bool
     */
    private function validationMessageText($data)
    {
        if (empty($data['user_id']) && empty($data['group_id'])) return false;
        if ((!isset($data['text']) || $data['text'] == '')) return false;
        if (!empty($data['group_id'])) {
            $senderId = Websocket::getUserId();
            $memberExists = app(\App\Repositories\ChatRepository::class)->checkMemberExistsInGroup($data['group_id'], $senderId);
            if (!$memberExists) return false;
        }
        return true;
    }

    /**
     * Validation Message File
     *
     * @param $data
     * @return bool
     */
    private function validationMessageFile($data)
    {
        if (empty($data['user_id']) && empty($data['group_id'])) return false;
        if (empty($data['files'])) return false;
        foreach ($data['files'] as $file) {
            if (empty($file['name']) || empty($file['base64'])) return false;
        }
        if (!empty($data['group_id'])) {
            $senderId = Websocket::getUserId();
            $memberExists = app(\App\Repositories\ChatRepository::class)->checkMemberExistsInGroup($data['group_id'], $senderId);
            if (!$memberExists) return false;
        }
        return true;
    }

    /**
     * Prepare Message Text
     *
     * @param $data
     * @return array
     */
    private function prepareMessageText($data)
    {
        return [
            'fk_sender_id'      => Websocket::getUserId(),
            'fk_receiver_id'    => @$data['user_id'],
            'fk_group_id'       => @$data['group_id'],
            'fk_file_id'        => null,
            'message_type'      => 'text',
            'message_content'   => $data['text'],
        ];
    }

    /**
     * Prepare Message File
     *
     * @param $data
     * @return array
     */
    private function prepareMessageFile($data)
    {
        $insert = [];

        $message = [
            'fk_sender_id'      => Websocket::getUserId(),
            'fk_receiver_id'    => @$data['user_id'],
            'fk_group_id'       => @$data['group_id'],
            'message_type'      => 'image',
            'message_content'   => '',
        ];

        if (@$data['group_id']) {
            $path = 'chats/group_' . $message['fk_group_id'] . '/';
        }else {
            $path = 'chats/chat_' . $message['fk_sender_id'] . '_' . $message['fk_receiver_id'] . '/';
        }

        $filesIds = $this->storeFiles($data['files'], $path);

        foreach ($filesIds as $filesId) {
            $message['fk_file_id'] = $filesId;
            $insert[] = $message;
        }

        return $insert;
    }

    /**
     * Convert and Store Messages Files in DB
     *
     * @param $files
     * @param $path
     * @return array
     */
    private function storeFiles($files, $path)
    {
        $ids = [];
        foreach ($files as $file) {
            $img = $this->convertBase64ToImage($file['base64'], $path);
            if ($img) {
                $imageArray = [
                    'original_name'     => $file['name'],
                    'file_path'         => $img
                ];
                $ids[] = app(\App\Repositories\ChatRepository::class)->saveMessageFiles($imageArray);
            }
        }
        return $ids;
    }

    /**
     * Convert base64 To Image File
     * For Insert Image Path To DB
     *
     * @param $base64
     * @param $path
     * @return bool
     * @throws \Exception
     */
    private function convertBase64ToImage($base64, $path)
    {
        $extension = explode('/', explode(':', substr($base64, 0, strpos($base64, ';')))[1])[1];

        if (!in_array(strtolower($extension), ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'svg'])) {
            return false;
        }

        $replace = substr($base64, 0, strpos($base64, ',')+1);

        $image = str_replace($replace, '', $base64);

        $image = str_replace(' ', '+', $image);

        $imagePath = $path . bin2hex(random_bytes(20)).'.'.$extension;

        Storage::disk('public')->put($imagePath, base64_decode($image));

        return $imagePath;
    }

    /**
     * mark as read messages and emit again all unread last messages
     *
     * @param $last_message_id
     * @param null $user_id
     * @param null $group_id
     */
    public function unreadMessage($last_message_id, $user_id=null, $group_id=null)
    {
        $unreadMessages = app(\App\Repositories\ChatRepository::class)->readMessages($last_message_id, $user_id, $group_id);

        Websocket::emit('unread_messages', $unreadMessages);

        $this->emitTotalUnreadMessage();
    }

    /**
     * Get total unread messages
     * 
     */
    public function emitTotalUnreadMessage()
    {
        $totalUnreadMessages = app(\App\Repositories\ChatRepository::class)->getTotalUnreadMessage();

        Websocket::emit('total_unread_messages', $totalUnreadMessages);
    }

    /**
     * Notification Users
     * Notify Users By [user id]
     *
     * @param $request
     * @return bool|\Illuminate\Support\MessageBag
     */
    public function notifyUsers($request)
    {
        $requestData = $request->only(['userId', 'content']);

        $validator = Validator::make($requestData,[
            'userId' => 'required|array',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        return Websocket::toUserId($requestData['userId'])->emit('notification', $requestData['content']);
    }

    /**
     * Emit any content to custom event name and specific users
     *
     * @param $request
     * @return bool|\Illuminate\Support\MessageBag
     */
    public function emit($request)
    {
        $requestData = $request->only(['userId', 'event_name', 'content']);

        $validator = Validator::make($requestData, [
            'userId' => 'sometimes|array',
            'event_name' => 'required|string',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        if (empty($requestData['userId'])) {
            $users = $this->getOnlineUsers();
        }else {
            $users = $requestData['userId'];
        }

        return Websocket::toUserId($users)->emit($requestData['event_name'], $requestData['content']);
    }
}