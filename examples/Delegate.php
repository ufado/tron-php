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
$tronApi = new Api(new Client(['base_uri' => 'https://nile.trongrid.io']));
$trxWallet = new TRX($tronApi);

$tronSecret = "0000000";//波场私钥
$tronAddress = "Txxxxxx";//波场公钥（波场地址）
//转换成Address类
$fromAddr = $trc20Wallet->privateKeyToAddress($tronSecret);//发起地址
$toAddr = new Address(
    $tronAddress,
    'test',
    $trc20Wallet->tron->address2HexString($tronAddress)
);//接受地址
//以下全部为质押2.0接口
$trxWallet->delegate($fromAddr,$toAddr,1);//代理1trx产生的能量
$trxWallet->undelegate($fromAddr,$toAddr,1);//收回1trx产生的能量
$trxWallet->delegate($fromAddr,$toAddr,1,"BANDWITH");//代理1trx产生的带宽
$trxWallet->undelegate($fromAddr,$toAddr,"BANDWITH");//收回1trx产生的带宽
$trxWallet->delegate($fromAddr,$toAddr,1,"ENERGY",true,1200);//代理1trx产生的能量,锁定期1小时，单位为3秒
$trxWallet->tron->getdelegatedresourceaccountindexv2($fromAddr->address);//获取全部已经代理的资源
//下面价格计算公式见 https://tronscan.org/#/tools/tronstation
//必须使用一个激活的地址查询
$trxWallet->getFrozenEnergyPrice($fromAddr);//质押1trx获得的能量 例如12.369
$trxWallet->getNetEnergyPrice($fromAddr);//质押1trx获得的带宽 例如1.197