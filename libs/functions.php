<?php

function BaleRequest(string $method, array $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://tapi.bale.ai/bot' . API_KEY . '/' . $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    return json_decode($response);
}

function sendMessage($chat_id, $text, $reply_markup = null, $message_id = null)
{
    return BaleRequest('sendMessage', [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $reply_markup,
        'reply_to_message_id' => $message_id
    ]);
}

function editMessage($chat_id, $message_id, $text, $reply_markup = null)
{
    return BaleRequest('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'reply_markup' => $reply_markup
    ]);
}

function deleteMessage($chat_id, $message_id)
{
    return BaleRequest(
        'deleteMessage',
        [
            'chat_id' => $chat_id,
            'message_id' => $message_id
        ]
    );
}

function forwardMessage($chat_id, $from_id, $message_id)
{
    return BaleRequest('ForwardMessage', [
        'chat_id' => $chat_id,
        'from_chat_id' => $from_id,
        'message_id' => $message_id
    ]);
}

function sendPayment($chat_id, $title, $description, $payload, $provider_token, $prices, $photo_url = null)
{
    return BaleRequest('sendInvoice', [
        'chat_id' => $chat_id,
        'title' => $title,
        'description' => $description,
        'payload' => $payload,
        'provider_token' => $provider_token,
        'prices' => json_encode($prices),
        'photo_url' => $photo_url
    ]);
}

function getChat($chat_id)
{
    return BaleRequest('getChat', [
        'chat_id' => $chat_id
    ]);
}

function getChatMember($chat_id, $user_id)
{
    return BaleRequest('getChatMember', [
        'chat_id' => $chat_id,
        'user_id' => $user_id
    ]);
}

function getUser($chat_id)
{
    global $pdo;
    $query = "SELECT * FROM `users` WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$chat_id]);
    return $stmt->fetch();
}

function setStep($chat_id, $step)
{
    global $pdo;
    $query = "UPDATE `users` SET `step` = ? WHERE `chat_id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$step, $chat_id]);
}

function debug($from_id, $value)
{
    $update = print_r($value, true);
    sendMessage($from_id, $update);
}

function convertPersianToEnglishNumbers($string) {
    $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $english = ['0','1','2','3','4','5','6','7','8','9'];
    return str_replace($persian, $english, $string);
}

function leaveChat($chat_id)
{
    return BaleRequest('leaveChat', [
        'chat_id' => $chat_id,
    ]);
}

function answerToPay($pre_checkout_query_id)
{
    return BaleRequest('answerPreCheckoutQuery', [
        'pre_checkout_query_id' => $pre_checkout_query_id,
        'ok' => true
    ]);
}

function settings($key)
{
    global $pdo;
    $query = "SELECT * FROM `settings` WHERE `_key` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$key]);
    return $stmt->fetch()->_value;
}
