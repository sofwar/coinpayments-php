<?php
/**
 * User: SofWar (ya.sofwar@yandex.com).
 */

namespace SofWar\CoinPayments;

use GuzzleHttp\Client;

class Request
{
    const API_VERSION = 1;
    const API_HOST = 'https://www.coinpayments.net';
    const API_PATH = '/api.php';

    private $publicKey;
    private $privateKey;

    public function __construct($publicKey, $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    public function request($parameters)
    {
        $reqs = $this->getSettings($parameters);

        if ($reqs === false) {
            throw new \InvalidArgumentException('No such method '.($parameters['cmd'] ?? ' - '));
        }

        if (\count($reqs)) {
            foreach ($reqs as $req) {
                if (!array_key_exists($req, $parameters)) {
                    throw new \InvalidArgumentException('Missing options:  '.$req);
                }
            }
        }

        $parameters['version'] = self::API_VERSION;
        $parameters['format'] = 'json';
        $parameters['key'] = $this->publicKey;

        $client = new Client([
            'base_uri'    => self::API_HOST,
            'http_errors' => false,
            'headers'     => $this->_getPrivateHeaders($parameters),
            'query'       => $parameters,
            'form_params' => $parameters,
        ]);

        $request = $client->post(self::API_PATH);

        if ($request->getStatusCode() === 200) {
            $data = json_decode($request->getBody()->getContents());

            if (isset($data->error) && $data->error !== 'ok') {
                throw new ApiException($data->error);
            }

            return $data->result ?? null;
        }

        throw new ApiException($request->getBody()->getContents());
    }

    private function getSettings($options)
    {
        $cmd = $options['cmd'] ?? false;

        switch ($cmd) {
            case 'get_basic_info':
                return [];
            case 'get_tx_ids':
                return [];
            case 'get_deposit_address':
                return ['currency'];
            case 'get_callback_address':
                return ['currency'];
            case 'create_transfer':
                return ['amount', 'currency', 'merchant | pbntag'];
            case 'convert':
                return ['amount', 'from', 'to'];
            case 'get_withdrawal_history':
                return [];
            case 'get_conversion_info':
                return ['id'];
            case 'get_pbn_info':
                return ['pbntag'];
            case 'get_pbn_list':
                return [];
            case 'update_pbn_tag':
                return ['tagid'];
            case 'claim_pbn_tag':
                return ['tagid', 'name'];
            case 'get_withdrawal_info':
                return ['id'];
            case 'get_tx_info':
                return ['txid'];
            case 'get_tx_info_multi':
                return ['txid'];
            case 'create_withdrawal':
                return ['amount', 'currency', 'address'];
            case 'create_mass_withdrawal':
                return [];
            case 'create_transaction':
                return ['amount', 'currency1', 'currency2'];
            case 'rates':
                return [];
            case 'balances':
                return [];
            default:
                return false;
        }
    }

    private function _getPrivateHeaders($parameters)
    {
        $paramString = http_build_query($parameters, '', '&');
        $signature = hash_hmac('sha512', $paramString, $this->privateKey);

        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'HMAC'         => $signature,
        ];
    }
}
