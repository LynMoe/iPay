<?php
/**
 * Created by PhpStorm.
 * User: XiaoLin
 * Date: 2019/7/21
 * Time: 22:02
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Units/Database.php';

date_default_timezone_set('Asia/Shanghai');

function output($data)
{
    header('Content-type: application/json');
    echo json_encode($data);
}

if (file_exists(__DIR__ . '/.token') &&
    ($token = json_decode((file_get_contents(__DIR__ . '/.token')),true))['time'] + 1800 > time())
    $_ENV['token'] = $token['auth_token'];
else {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://afdian.net/api/passport/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'account' => $_ENV['username'],
        'password' => $_ENV['password'],
        'mp_token' => -1,
    ]));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'deflate');

    $headers = array();
    $headers[] = 'Origin: https://afdian.net';
    $headers[] = 'Accept-Encoding: gzip, deflate, br';
    $headers[] = 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8';
    $headers[] = 'X-Requested-With: XMLHttpRequest';
    $headers[] = 'Pragma: no-cache';
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36';
    $headers[] = 'Content-Type: application/json;charset=UTF-8';
    $headers[] = 'Accept: application/json, text/plain, */*';
    $headers[] = 'Cache-Control: no-cache';
    $headers[] = 'Authority: afdian.net';
    $headers[] = 'Referer: https://afdian.net/login';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    if (($result = json_decode($result,true))['ec'] == 200)
    {
        $_ENV['token'] = $result['data']['auth_token'];
        file_put_contents(__DIR__ . '/.token',json_encode([
            'auth_token' => $_ENV['token'],
            'time' => time(),
        ]));
    }
}

unset($token,$ch,$result,$headers);