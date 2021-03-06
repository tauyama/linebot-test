<?php

require('../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$bot = new CU\LineBot();

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));

$app->before(function (Request $request) use($bot) {
    // Signature validation
    $request_body = $request->getContent();
    $signature = $request->headers->get('X-LINE-CHANNELSIGNATURE');
    if (!$bot->isValid($signature, $request_body)) {
        return new Response('Signature validation failed.', 400);
    }
});

$app->post('/callback', function (Request $request) use ($app, $bot) {
    // Let's hack from here!
    $body = json_decode($request->getContent(), true);
    $time = time() ;
    $filename = 'test.txt';
    $file = file_get_contents($filename);
    
    foreach ($body['result'] as $obj) {
        $app['monolog']->addInfo(sprintf('obj: %s', json_encode($obj)));
        $from = $obj['content']['from'];
        $content = $obj['content'];

        if ($content['text']) {
            $fp = fopen("test.txt", "w");
            fwrite($fp, "ファイルへの書き込みサンプル");
            fclose($fp);

            $bot->sendText($from, sprintf('%s%d年%d月%d日%d時%d分ですa', $content['text'],date( "Y" , $time ),date( "m" , $time ),date( "d" , $time ),date( "G" , $time ),date( "i" , $time )));
            $bot->sendText($from, sprintf('%sあ%s', $content['text'],$file));
        }
    }

    return 0;
});

$app->run();
