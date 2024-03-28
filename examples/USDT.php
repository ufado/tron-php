<?php
require("./vendor/autoload.php");
use GuzzleHttp\Client;
use IEXBase\TronAPI\Exception\ErrorException;
use IEXBase\TronAPI\Exception\TronException;
use Tron\Exceptions\TransactionException;
use Tron\Address;
use Tron\Api;
use Tron\Exceptions\TronErrorException;
use Tron\TRC20;
use Tron\TRX;
$tronApi = new Api(new Client(['base_uri' => 'https://api.trongrid.io']));
$trc20Wallet = new TRC20($tronApi,[
    'contract_address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',// USDT TRC20
    'decimals' => 6,
]);

$tronSecret = "0000000";//波场私钥
$tronAddress = "Txxxxxx";//波场公钥（波场地址）
//转换成Address类
$fromAddr = $trc20Wallet->privateKeyToAddress($tronSecret);//发起地址
$toAddr = new Address(
    $tronAddress,
    'test',
    $trc20Wallet->tron->address2HexString($tronAddress)
);//接受地址

var_dump($fromAddr->address);//Adress类中获取公钥
$usdt = $trc20Wallet->balance($fromAddr);//获取usdt余额
var_dump($usdt);

try {
    $transferData = $trc20Wallet->transfer($fromAddr,$toAddr,1);//转账1usdt
} catch (TronException $e) {
    debug_print_backtrace();
    var_dump($e->getMessage(), $e->getCode());
}catch (\Exception $e){
    debug_print_backtrace();
    var_dump($e->getMessage());
}
var_dump($transferData);
