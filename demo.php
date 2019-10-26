<?php

include 'kTelegramSaver.php';

$saver = new kTelegramSaver('your phone', __DIR__ . '/files');

$message_history = $saver->getChatHistory(1347758174, 10)['messages'];

$to_save = $saver->getFilteredMessage($message_history, [
    'tagsForSave' => [
        'soles',
        'barefoot',
        'foot_hold'

    ], 'tagsForIgnore' => [
        'toes',
        'toenails',
        'the_pose'
    ],
    'removeText' => [
        '🔎navigation:',
        '🌐By-animehub.cc'
    ]
]);

print_r($saver->saveFilteredMessages($to_save['items']));