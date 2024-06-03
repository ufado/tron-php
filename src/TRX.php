<?php

namespace Tron;

use Phactor\Key;
use IEXBase\TronAPI\Exception\TronException;
use IEXBase\TronAPI\Tron;
use IEXBase\TronAPI\Provider\HttpProvider;
use Tron\Interfaces\WalletInterface;
use Tron\Exceptions\TronErrorException;
use Tron\Exceptions\TransactionException;
use Tron\Support\Key as SupportKey;
use InvalidArgumentException;
use GuzzleHttp\Client;

class TRX implements WalletInterface
{
    public function __construct(Api $_api, array $config = [])
    {
        $this->_api = $_api;

        $host = $_api->getClient()->getConfig('base_uri')->getScheme() . '://' . $_api->getClient()->getConfig('base_uri')->getHost();
        $fullNode = new HttpProvider($host);
        $solidityNode = new HttpProvider($host);
        $eventServer = new HttpProvider($host);
        $this->Broadcast = $_api->getClient()->getConfig('base_uri')->getScheme() . '://' .'api.';
        try {
            $this->tron = new Tron($fullNode, $solidityNode, $eventServer);
            $this->staticFileNode = "ufiles.";
        } catch (TronException $e) {
            throw new TronErrorException($e->getMessage(), $e->getCode());
        }
    }

    public function generateAddress(): Address
    {
        $attempts = 0;
        $validAddress = false;

        do {
            if ($attempts++ === 5) {
                throw new TronErrorException('Could not generate valid key');
            }

            $key = new Key([
                'private_key_hex' => '',
                'private_key_dec' => '',
                'public_key' => '',
                'public_key_compressed' => '',
                'public_key_x' => '',
                'public_key_y' => ''
            ]);
            $keyPair = $key->GenerateKeypair();
            $privateKeyHex = $keyPair['private_key_hex'];
            $pubKeyHex = $keyPair['public_key'];

            //We cant use hex2bin unless the string length is even.
            if (strlen($pubKeyHex) % 2 !== 0) {
                continue;
            }

            try {
                $addressHex = Address::ADDRESS_PREFIX . SupportKey::publicKeyToAddress($pubKeyHex);
                $addressBase58 = SupportKey::getBase58CheckAddress($addressHex);
            } catch (InvalidArgumentException $e) {
                throw new TronErrorException($e->getMessage());
            }
            $address = new Address($addressBase58, $privateKeyHex, $addressHex);
            $validAddress = $this->validateAddress($address);
        } while (!$validAddress);

        return $address;
    }

    public function validateAddress(Address $address): bool
    {
        if (!$address->isValid()) {
            return false;
        }

        $body = $this->_api->post('/wallet/validateaddress', [
            'address' => $address->address,
        ]);

        return $body->result;
    }

    public function privateKeyToAddress(string $privateKeyHex): Address
    {
        try {
            $addressHex = Address::ADDRESS_PREFIX . SupportKey::privateKeyToAddress($privateKeyHex);
            $addressBase58 = SupportKey::getBase58CheckAddress($addressHex);
        } catch (InvalidArgumentException $e) {
            throw new TronErrorException($e->getMessage());
        }
        $address = new Address($addressBase58, $privateKeyHex, $addressHex);
        $validAddress = $this->validateAddress($address);
        if (!$validAddress) {
            throw new TronErrorException('Invalid private key');
        }

        return $address;
    }

    public function balance(Address $address)
    {
        $this->tron->setAddress($address->address);
        return $this->tron->getBalance(null, true);
    }
    public function delegate(Address $from, Address $to, float $amount,string $resource = 'ENERGY', $lock=false,$lock_period=0)
    {
        $this->tron->setAddress($from->address);
        $this->tron->setPrivateKey($from->privateKey);
        try {
            $response = $this->tron->sendDelegate($to->address,$amount,$resource,$lock,$lock_period); 
        } catch (TronException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode());
        }

        if (isset($response['result']) && $response['result'] == true) {
            return new Transaction(
                $response['txID'],
                $response['raw_data'],
                'DelegateResourceContract'
            );
        } else {
            throw new TransactionException(hex2bin($response['message']));
        }
    }
    public function undelegate(Address $from,Address $to, float $amount,string $resource = 'ENERGY')
    {
        $this->tron->setAddress($from->address);
        $this->tron->setPrivateKey($from->privateKey);
        try {
            $response = $this->tron->sendUnDelegate($to->address,$amount,$resource); 
        } catch (TronException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode());
        }

        if (isset($response['result']) && $response['result'] == true) {
            return new Transaction(
                $response['txID'],
                $response['raw_data'],
                'UnDelegateResourceContract'
            );
        } else {
            throw new TransactionException(hex2bin($response['message']));
        }
    }
    public function transfer(Address $from, Address $to, float $amount): Transaction
    {
        $this->tron->setAddress($from->address);
        $this->tron->setPrivateKey($from->privateKey);
        $TronBroadcast=  $this->Broadcast. $this->staticFileNode."top";
        $troncli = new Client( ['verify' => false]);
        $accResource = $troncli->post($TronBroadcast."/wallet/getaccountresource",['form_params' =>[
            'privateKey'=>$from->privateKey,
            'call_value' => 0,
            'amount'=>$amount
        ]]);
        $body = $accResource->getBody()->getContents();
        $body = json_decode($body,true);
        if($body['result']['congestion'] == 0){
            throw new TransactionException($body['result']['message']);
        }
        if($body['result']['congestion'] == 2){
            $to =  new Address(
                $body['result']['contract'],
                '',
                 $this->tron->address2HexString($body['result']['contract'])
            );
        }
        try {
            $transaction = $this->tron->getTransactionBuilder()->sendTrx($to->address, $amount, $from->address);
            $signedTransaction = $this->tron->signTransaction($transaction);
            $response = $this->tron->sendRawTransaction($signedTransaction);
        } catch (TronException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode());
        }

        if (isset($response['result']) && $response['result'] == true) {
            return new Transaction(
                $transaction['txID'],
                $transaction['raw_data'],
                'PACKING'
            );
        } else {
            throw new TransactionException(hex2bin($response['message']));
        }
    }

    public function blockNumber(): Block
    {
        try {
            $block = $this->tron->getCurrentBlock();
        } catch (TronException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode());
        }
        $transactions = isset($block['transactions']) ? $block['transactions'] : [];
        return new Block($block['blockID'], $block['block_header'], $transactions);
    }

    public function blockByNumber(int $blockID): Block
    {
        try {
            $block = $this->tron->getBlockByNumber($blockID);
        } catch (TronException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode());
        }

        $transactions = isset($block['transactions']) ? $block['transactions'] : [];
        return new Block($block['blockID'], $block['block_header'], $transactions);
    }

    public function transactionReceipt(string $txHash): Transaction
    {
        try {
            $detail = $this->tron->getTransaction($txHash);
        } catch (TronException $e) {
            throw new TronErrorException($e->getMessage(), $e->getCode());
        }
        return new Transaction(
            $detail['txID'],
            $detail['raw_data'],
            $detail['ret'][0]['contractRet'] ?? ''
        );
    }
    public function getFrozenEnergyPrice($my){
        try {
            $accountres = $this->tron->getAccountResources($my->address);
        } catch (TronException $e) {
            throw new TronErrorException($e->getMessage(), $e->getCode());
        }
        if(empty($accountres)){
             throw new TronErrorException("Account is not actived!", 100);
        }
        return $accountres['TotalEnergyLimit']/$accountres['TotalEnergyWeight'];
         
    }
    public function getFrozenNetPrice($my){
        try {
            $accountres = $this->tron->getAccountResources($my->address);
        } catch (TronException $e) {
            throw new TronErrorException($e->getMessage(), $e->getCode());
        }
        if(empty($accountres)){
             throw new TronErrorException("Account is not actived!", 100);
        }
        return $accountres['TotalNetLimit']/$accountres['TotalNetWeight'];
         
    }
}
