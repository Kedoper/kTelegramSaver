# kTelegramSaver - console app for save images by tags

### Requirements:
1) PHP ^7.1

### Demo:

For example the Telegram channel with this id is taken `1347758174`. [Channel Link](https://t.me/nozhk1_2d).

If you use a cloud password, it must be disabled, otherwise it will not work
````php
include 'kTelegramSaver.php';
  /*
     First: Your phone number
     Second: Patch for save images
  */
  $saver = new kTelegramSaver('your phone', __DIR__ . '/files');

 /*
     First: Chanel id
     Second: message limit
  */
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
````

### Run:

1) Download the latest release and unzip to any convenient location
1) Run script by command shell ``php demo.php`` (run demo.php for example)
1) Enter the code you received from Telegram ``Enter the code you received: ``