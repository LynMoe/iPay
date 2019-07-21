<?php
/**
 * Created by PhpStorm.
 * User: XiaoLin
 * Date: 2019/7/21
 * Time: 21:55
 */

require_once __DIR__ . '/init.php';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://afdian.net/api/my/dashboard');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_ENCODING, 'deflate');

$headers = array();
$headers[] = 'Pragma: no-cache';
$headers[] = 'Cookie: auth_token=' . $_ENV['token'];
$headers[] = 'Accept-Encoding: gzip, deflate, br';
$headers[] = 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8';
$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36';
$headers[] = 'Accept: application/json, text/plain, */*';
$headers[] = 'Cache-Control: no-cache';
$headers[] = 'Authority: afdian.net';
$headers[] = 'X-Requested-With: XMLHttpRequest';
$headers[] = 'Referer: https://afdian.net/api/my/dashboard';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = json_decode(curl_exec($ch),true);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);

//var_dump($result);

if ($result['ec'] == 200)
{
    foreach ($result['data']['sponsored_history'] as $item)
    {
        if ($item['status'] == 2)
        {
            $result = (new Database())->completeOrder($item['remark']);
            if ($result)
                echo "Order {$item['remark']} completed.\n";
        }

    }
}