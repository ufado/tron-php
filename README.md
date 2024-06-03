<h1 align="center">波场开发包 php版</h1>

## 概述

波场开发包目前支持波场的 TRX 和 TRC20 中生成地址，发起转账，离线签名，资源代理和收回，资源价格查询等功能。正在持续更新，将会支持更多的功能，已修复原版不少bug，将会持续维护。

## 特点

1. 方法调用快捷方便
1. 兼容 TRON 网络中 TRX 货币和 TRC 系列所有通证
1. 支持最新的质押2.0中的资源代理和资源回收
1. 支持实时获取质押获得的资源数量，例如质押1trx获得的能量
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
- 资源代理`delegate(Address $from, Address $to, float $amount,string $resource = 'ENERGY', $lock=false,$lock_period=0)`
- 资源收回`undelegate(Address $from,Address $to, float $amount,string $resource = 'ENERGY')`
- 质押1trx获得的能量`getFrozenEnergyPrice(Address $my)`
- 质押1trx获得的带宽`getFrozenNetPrice(Address $my)`

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

[Delegate.php](./examples/Delegate.php)

```php
//以下全部为质押2.0接口
$trxWallet->delegate($fromAddr,$toAddr,1);//代理1trx产生的能量
$trxWallet->undelegate($fromAddr,$toAddr,1);//收回1trx产生的能量
$trxWallet->delegate($fromAddr,$toAddr,1,"BANDWITH");//代理1trx产生的带宽
$trxWallet->undelegate($fromAddr,$toAddr,"BANDWITH");//收回1trx产生的带宽
$trxWallet->delegate($fromAddr,$toAddr,1,"ENERGY",true,1200);//代理1trx产生的能量,锁定期1小时，单位为3秒
$trxWallet->tron->getdelegatedresourceaccountindexv2($fromAddr->address);//获取全部已经代理的资源
$trxWallet->getFrozenEnergyPrice($toAddr);//质押1trx获得的能量 例如12.369
$trxWallet->getNetEnergyPrice($toAddr);//质押1trx获得的带宽 例如1.197
```



## 感谢

| 开发者名称 | 描述 | 应用场景 |
| :-----| :---- | :---- |
| [iexbase/tron-api](https://github.com/iexbase/tron-api) | 波场官方文档推荐 PHP 扩展包 | 波场基础Api |
| [Fenguoz](https://github.com/Fenguoz/) | 波场PHP 实现 | 波场基础Api |
| [ufado/tron-api](https://github.com/ufado/tron-api) | 基于iexbase的自行维护扩展包 | 波场基础Api |

## 联系

项目合作 项目开发 源码定制 请联系
Https://t.me/ufado_bot