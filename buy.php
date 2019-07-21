<?php
/**
 * Created by PhpStorm.
 * User: XiaoLin
 * Date: 2019/7/21
 * Time: 21:21
 */

require_once __DIR__ . '/init.php';

function generateRandomString($length = 64) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

$price = 5;
$remark = generateRandomString();
$payment = 'wxpay_qr'; //wxpay_qr

$ch = curl_init();

$param = "{\"plan_id\":\"\",\"month\":1,\"total_amount\":{$price},\"out_trade_no\":\"\",\"pay_type\":\"{$payment}\",\"code\":\"\",\"user_id\":\"{$_ENV['user_id']}\",\"per_month\":\"{$price}\",\"remark\":\"{$remark}\",\"mp_token\":-1,\"show_amount\":{$price}}";

curl_setopt($ch, CURLOPT_URL, 'https://afdian.net/api/order/create-order');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

$headers = array();
$headers[] = 'Origin: https://afdian.net';
$headers[] = 'Accept-Encoding: gzip, deflate, br';
$headers[] = 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8';
$headers[] = 'X-Requested-With: XMLHttpRequest';
$headers[] = 'Cookie: auth_token=' . $_ENV['token'];
$headers[] = 'Pragma: no-cache';
$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36';
$headers[] = 'Content-Type: application/json;charset=UTF-8';
$headers[] = 'Accept: application/json, text/plain, */*';
$headers[] = 'Cache-Control: no-cache';
$headers[] = 'Authority: afdian.net';
$headers[] = 'Referer: https://afdian.net/order/create?user_id=' . $_ENV['user_id'];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = json_decode(curl_exec($ch),true);
if (curl_errno($ch) || !$result) {
    output([
        'code' => 500,
        'msg' => '订单生成错误, 请稍后再试',
    ]);
}
curl_close($ch);

if ($result['ec'] == 200)
{
    (new Database())->insertOrder($remark,$price,$payment,$result['data']['out_trade_no'],$result['data']['redirect_url']);
    output([
        'code' => 0,
        'msg' => '订单生成成功, 请支付',
        'data' => [
            'payment' => ($payment == 'alipay') ? 'alipay' : 'weixin',
            'redirect_url' => $result['data']['redirect_url'],
            'order_id' => $remark,
        ],
    ]);
} else
    output([
        'code' => 500,
        'msg' => '订单生成错误, 请稍后再试',
    ]);