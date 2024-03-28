<h1 align="center">波场开发包 php版</h1>

## 概述

波场开发包目前支持波场的 TRX 和 TRC20 中生成地址，发起转账，离线签名等功能。正在持续更新，将会支持更多的功能，已修复[iexbase/tron-api](https://github.com/iexbase/tron-api)中的不少bug，将会持续维护。

## 特点

1. 方法调用快捷方便
1. 兼容 TRON 网络中 TRX 货币和 TRC 系列所有通证
1. 接口可灵活增减
1. 速度迅速 算法经过专门优化
1. 持续更新 始终跟进波场新功能

## 支持方法

- 生成地址 `generateAddress()`
- 验证地址 `validateAddress(Address $address)`
- 根据私钥得到地址 `privateKeyToAddress(string $privateKeyHex)`
- 查询余额 `balance(Address $address)`
- 交易转账(离线签名) `transfer(Address $from, Address $to, float $amount)`
- 查询最新区块 `blockNumber()`
- 根据区块链查询信息 `blockByNumber(int $blockID)`
- 根据交易哈希查询信息 `transactionReceipt(string $txHash)`

## 快速开始

### 安装

``` php
composer require ufado/tron-php
```

### 接口调用

完整代码请查阅/examples下的文件

[USDT.php](./examples/USDT.php)

``` php
//usdt转账
$tronSecret = "0000000";//波场私钥
$tronAddress = "Txxxxxx";//波场公钥（波场地址）
//转换成Address类
$fromAddr = $trc20Wallet->privateKeyToAddress($tronSecret);//发起地址
$toAddr = new Address(
    $tronAddress,
    '',
    $trc20Wallet->tron->address2HexString($tronAddress)
);//接受地址
$usdt = $trc20Wallet->balance($fromAddr);//获取usdt余额
$transferData = $trc20Wallet->transfer($fromAddr,$toAddr,1);//转账1usdt
```

[Trx.php](./examples/Trx.php)

```php

$tronSecret = "0000000";//波场私钥
$tronAddress = "Txxxxxx";//波场公钥（波场地址）
//转换成Address类
$fromAddr = $trxWallet->privateKeyToAddress($tronSecret);//发起地址
$toAddr = new Address(
    $tronAddress,
    '',
    $trxWallet->tron->address2HexString($tronAddress)
);//接受地址

$trx = $trxWallet->balance($fromAddr);//获取trx余额
$transferData = $trxWallet->transfer($fromAddr,$toAddr,1); //转账1trx
```



## 感谢

| 开发者名称 | 描述 | 应用场景 |
| :-----| :---- | :---- |
| [iexbase/tron-api](https://github.com/iexbase/tron-api) | 波场官方文档推荐 PHP 扩展包 | 波场基础Api |
| [Fenguoz](https://github.com/Fenguoz/) | 波场PHP 实现 | 波场基础Api |