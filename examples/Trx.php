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

$tronApi = new Api(new Client(['base_uri' => 'https://api.trongrid.io']));//正式链
$trxWallet = new TRX($tronApi);

$tronSecret = "0000000";//波场私钥
$tronAddress = "Txxxxxx";//波场公钥（波场地址）
//转换成Address类
$fromAddr = $trxWallet->privateKeyToAddress($tronSecret);//发起地址
$toAddr = new Address(
    $tronAddress,
    'test',
    $trxWallet->tron->address2HexString($tronAddress)
);//接受地址

var_dump($fromAddr->address);//Adress类中获取公钥
$trx = $trxWallet->balance($fromAddr);//获取trx余额
var_dump($trx);

try {
    $transferData = $trxWallet->transfer($fromAddr,$toAddr,1); //转账1trx
} catch (TronException $e) {
    debug_print_backtrace();
    var_dump($e->getMessage(), $e->getCode());
}catch (\Exception $e){
    debug_print_backtrace();
    var_dump($e->getMessage());
}
var_dump($transferData);
