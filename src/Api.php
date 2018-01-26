<?php
/**
 * User: SofWar (ya.sofwar@yandex.com)
 */

namespace SofWar\CoinPayments;

class Api
{
    public $client;
    private $publicKey;
    private $privateKey;

    public function __construct($publicKey, $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;

        $this->client = new Request($publicKey, $privateKey);
    }


    /**
     * Coin Balances
     *
     * @param int $all - If set to 1, the response will include all coins, even those with a 0 balance.
     * @return null
     * @throws ApiException
     */
    public function balances($all = 0)
    {
        return $this->client->request(['cmd' => 'balances', 'all' => $all]);
    }

    /**
     * Exchange Rates / Coin List
     * @param int $short - If set to 1, the response won't include the full coin names and number of confirms needed to save bandwidth.
     * @param int $accepted - If set to 1, the response will include if you have the coin enabled for acceptance on your Coin Acceptance Settings page.
     * @return null
     * @throws ApiException
     */
    public function rates($short = 0, $accepted = 0)
    {
        return $this->client->request(['cmd' => 'rates', 'short' => $short, 'accepted' => $accepted]);
    }

    /**
     * Get Transaction Information
     *
     * @param $txid - The transaction ID to query (API key must belong to the seller.) Note: It is recommended to handle IPNs instead of using this command when possible, it is more efficient and places less load on our servers.
     * @param $full - Set to 1 to also include the raw checkout and shipping data for the payment if available. (default: 0)
     * @return null
     * @throws ApiException
     */
    public function getTx($txid, $full = 0)
    {
        return $this->client->request(['cmd' => 'get_tx_info', 'txid' => $txid, 'full' => $full]);
    }

    /**
     * Get Basic Account Information
     *
     * @return null
     * @throws ApiException
     */
    public function getBasicInfo()
    {
        return $this->client->request(['cmd' => 'get_basic_info']);
    }

    /**
     * Get Callback Address
     *
     * @param $currency - The currency the buyer will be sending.
     * @param $ipn_url - URL for your IPN callbacks. If not set it will use the IPN URL in your Edit Settings page if you have one set.
     * @return null
     * @throws ApiException
     */
    public function getCallbackAddress($currency, $ipn_url = null)
    {
        if ($ipn_url === null) {
            return $this->getDepositAddress($currency);
        }

        return $this->client->request(['cmd' => 'get_callback_address', 'currency' => $currency, 'ipn_url' => $ipn_url]);
    }

    /**
     * Get Deposit Address
     *
     * @param $currency - The currency the buyer will be sending.
     * @return null
     * @throws ApiException
     */
    public function getDepositAddress($currency)
    {
        return $this->client->request(['cmd' => 'get_deposit_address', 'currency' => $currency]);
    }

    /**
     * Create Transaction
     *
     * @param $amount -    The amount of the transaction in the original currency (currency1 below).
     * @param $currency_1 - The original currency of the transaction.
     * @param $currency_2 - The currency the buyer will be sending. For example if your products are priced in USD but you are receiving BTC, you would use currency1=USD and currency2=BTC. currency1 and currency2 can be set to the same thing if you don't need currency conversion.
     * @param null $address - Optionally set the address to send the funds to (if not set will use the settings you have set on the 'Coins Acceptance Settings' page). Remember: this must be an address in currency2's network.
     * @param null $buyer_email -    Optionally (but highly recommended) set the buyer's email address. This will let us send them a notice if they underpay or need a refund. We will not add them to our mailing list or spam them or anything like that.
     * @param null $buyer_name - Optionally set the buyer's name for your reference.
     * @param null $item_name - Item name for your reference, will be on the payment information page and in the IPNs for the transaction.
     * @param null $item_number - Item number for your reference, will be on the payment information page and in the IPNs for the transaction.
     * @param null $invoice - Another field for your use, will be on the payment information page and in the IPNs for the transaction.
     * @param null $custom - Another field for your use, will be on the payment information page and in the IPNs for the transaction.
     * @param null $ipn_url - URL for your IPN callbacks. If not set it will use the IPN URL in your Edit Settings page if you have one set.
     * @return null
     * @throws ApiException
     */
    public function createTransaction($amount, $currency_1, $currency_2, $address = null, $buyer_email = null, $buyer_name = null, $item_name = null, $item_number = null, $invoice = null, $custom = null, $ipn_url = null)
    {
        return $this->client->request([
            'cmd' => 'create_transaction',
            'amount' => $amount,
            'currency_1' => $currency_1,
            'currency_2' => $currency_2,
            'address' => $address,
            'buyer_email' => $buyer_email,
            'buyer_name' => $buyer_name,
            'item_name' => $item_name,
            'item_number' => $item_number,
            'invoice' => $invoice,
            'custom' => $custom,
            'ipn_url' => $ipn_url,
        ]);
    }

    /**
     * Create Withdrawal
     *
     * @param $amount - The amount of the withdrawal in the currency below.
     * @param $currency - The cryptocurrency to withdraw. (BTC, LTC, etc.)
     * @param null $address - The address to send the funds to, either this OR pbntag must be specified. Remember: this must be an address in currency's network.
     * @param null $currency_2 - Optional currency to use to to withdraw 'amount' worth of 'currency2' in 'currency' coin. This is for exchange rate calculation only and will not convert coins or change which currency is withdrawn. For example, to withdraw 1.00 USD worth of BTC you would specify 'currency'='BTC', 'currency2'='USD', and 'amount'='1.00'
     * @param null $pbnTag - The $PayByName tag to send the withdrawal to, either this OR address must be specified. This will also override any destination tag specified.
     * @param null $descTag - The destination tag to use for the withdrawal (for Ripple.)
     * @param null $ipn_url - URL for your IPN callbacks. If not set it will use the IPN URL in your Edit Settings page if you have one set.
     * @param null $auto_confirm - If set to 1, withdrawal will complete without email confirmation.
     * @param null $note - This lets you set the note for the withdrawal.
     * @return null
     * @throws ApiException
     */
    public function createWithdrawal($amount, $currency, $address = null, $currency_2 = null, $pbnTag = null, $descTag = null, $ipn_url = null, $auto_confirm = null, $note = null)
    {
        return $this->client->request([
            'cmd' => 'create_withdrawal',
            'amount' => $amount,
            'currency' => $currency,
            'currency_2' => $currency_2,
            'address' => $address,
            'pbntag' => $pbnTag,
            'dest_tag' => $descTag,
            'auto_confirm' => $auto_confirm,
            'ipn_url' => $ipn_url,
            'note' => $note
        ]);
    }

    /**
     * Create Mass Withdrawal
     *
     * @param $withdrawal - The withdrawals are passed in an associative array called wd, each having the parameters from 'create_withdrawal' except auto_confirm which is always 1 in mass withdrawals. The key of each withdrawal is used to return the result (same as 'create_withdrawal' again.) The key can contain ONLY a-z, A-Z, and 0-9. Withdrawals with empty keys or containing other characters will be silently ignored.
     * @return null
     * @throws ApiException
     */
    public function createMassWithdrawal($withdrawal)
    {
        $options = [
            'cmd' => 'create_mass_withdrawal'
        ];

        foreach ($withdrawal as $i => $w) {
            $options['wd[wd' . $i . '][amount]'] = $w['amount'];
            $options['wd[wd' . $i . '][address]'] = $w['address'];
            $options['wd[wd' . $i . '][currency]'] = $w['currency'];
        }

        if (!count($options)) {
            return null;
        }

        return $this->client->request($options);
    }

    /**
     * Get Withdrawal Information
     *
     * @param $id - The withdrawal ID to query.
     * @return null
     * @throws ApiException
     */
    public function getWithdrawalInfo($id)
    {
        return $this->client->request(['cmd' => 'get_withdrawal_info', 'id' => $id]);
    }

    /**
     * Get Withdrawal History
     *
     * @param int $limit - The maximum number of withdrawals to return from 1-100. (default: 25)
     * @param int $start - What withdrawals # to start from (for iteration/pagination.) (default: 0, starts with your newest withdrawals.)
     * @param int $newer - Return withdrawals submitted at the given Unix timestamp or later. (default: 0)
     * @return null
     * @throws ApiException
     */
    public function getWithdrawalHistory($limit = 25, $start = 0, $newer = 0)
    {
        return $this->client->request([
            'cmd' => 'get_withdrawal_history',
            'limit' => $limit,
            'start' => $start,
            'newer' => $newer
        ]);
    }

    /**
     * Get Multiple Transaction Information
     *
     * @param $tx_id_array - Lets you query up to 25 transaction ID(s) (API key must belong to the seller.) Transaction IDs should be separated with a | (pipe symbol.) Note: It is recommended to handle IPNs instead of using this command when possible, it is more efficient and places less load on our servers.
     * @return null
     * @throws ApiException
     */
    public function getTxMulti($tx_id_array)
    {
        return $this->client->request(['cmd' => 'get_tx_info_multi', 'txid' => implode('|', $tx_id_array)]);
    }

    /**
     * Get Transaction IDs
     *
     * @param int $limit -    The maximum number of transaction IDs to return from 1-100. (default: 25)
     * @param int $start - What transaction # to start from (for iteration/pagination.) (default: 0, starts with your newest transactions.)
     * @param int $newer - Return transactions started at the given Unix timestamp or later. (default: 0)
     * @param int $all - By default we return an array of TX IDs where you are the seller for use with get_tx_info_multi or get_tx_info. If all is set to 1 returns an array with TX IDs and whether you are the seller or buyer for the transaction.
     * @return null
     * @throws ApiException
     */
    public function getTxList($limit = 25, $start = 0, $newer = 0, $all = 0)
    {
        return $this->client->request(['cmd' => 'get_tx_ids', 'limit' => $limit, 'start' => $start, 'newer' => $newer, 'all' => $all]);
    }

    /**
     * Create Transfer
     *
     * @param $amount - The amount of the transfer in the currency below.
     * @param $currency - The cryptocurrency to withdraw. (BTC, LTC, etc.)
     * @param null $merchant -    The merchant ID to send the funds to, either this OR pbntag must be specified. Remember: this is a merchant ID and not a username.
     * @param null $pbnTag - The $PayByName tag to send the funds to, either this OR merchant must be specified.
     * @param int $autoConfirm - If set to 1, withdrawal will complete without email confirmation.
     * @return null
     * @throws ApiException
     */
    public function createTransfer($amount, $currency, $merchant = null, $pbnTag = null, $autoConfirm = 1)
    {
        $params = [
            'cmd' => 'create_transfer',
            'auto_confirm' => $autoConfirm,
            'amount' => $amount,
            'currency' => $currency
        ];

        if ($merchant !== null) {
            $params['merchant'] = $merchant;
        }

        if ($pbnTag !== null) {
            $params['pbntag'] = $pbnTag;
        }

        return $this->client->request($params);
    }

    /**
     * Convert Coins
     *
     * @param $amount - The amount convert in the 'from' currency below.
     * @param $from -    The cryptocurrency in your Coin Wallet to convert from. (BTC, LTC, etc.)
     * @param $to - The cryptocurrency to convert to. (BTC, LTC, etc.)
     * @param null $address - The address to send the funds to. If blank or not included the coins will go to your CoinPayments Wallet.
     * @param null $descTag -    The destination tag to use for the withdrawal (for Ripple.) If 'address' is not included this has no effect.
     * @return null
     * @throws ApiException
     */
    public function convertCoins($amount, $from, $to, $address = null, $descTag = null)
    {
        $params = [
            'cmd' => 'convert',
            'amount' => $amount,
            'from' => $from,
            'to' => $to
        ];

        if ($address !== null) {
            $params['address'] = $address;
        }

        if ($descTag !== null) {
            $params['dest_tag'] = $descTag;
        }

        return $this->client->request($params);
    }

    /**
     * Get Conversion Information
     *
     * @param $id - The conversion ID to query.
     * @return null
     * @throws ApiException
     */
    public function getConversionInfo($id)
    {
        return $this->client->request(['cmd' => 'get_conversion_info', 'id' => $id]);
    }

    /**
     * Get $PayByName Profile Information
     *
     * @param $pbnTag - Tag to get information for, such as $CoinPayments or $Alex. Can be with or without a $ at the beginning.
     * @return null
     * @throws ApiException
     */
    public function getProfile($pbnTag)
    {
        return $this->client->request(['cmd' => 'get_pbn_info', 'pbntag' => $pbnTag]);
    }

    /**
     * Get $PayByName Tag List
     *
     * @return null
     * @throws ApiException
     */
    public function tagList()
    {
        return $this->client->request(['cmd' => 'get_pbn_list']);
    }

    /**
     * Update $PayByName Profile
     *
     * @param $tagID - The tag's unique ID (obtained from 'get_pbn_list'.
     * @param null $name - Name for the profile. If field is not supplied the current name will be unchanged.
     * @param null $email - Email for the profile. If field is not supplied the current email will be unchanged.
     * @param null $url - Website URL for the profile. If field is not supplied the current URL will be unchanged.
     * @param null $image - HTTP POST with a JPG or PNG image 250KB or smaller. This is an actual "multipart/form-data" file POST and not a URL to a file. If field is not supplied the current image will be unchanged.
     * @return null
     * @throws ApiException
     */
    public function updateTagProfile($tagID, $name = null, $email = null, $url = null, $image = null)
    {
        $params = [
            'cmd' => 'get_pbn_list',
            'tagid' => $tagID
        ];

        if ($name !== null) {
            $params['name'] = $name;
        }

        if ($email !== null) {
            $params['email'] = $email;
        }

        if ($url !== null) {
            $params['url'] = $url;
        }

        if ($image !== null) {
            $params['image'] = $image;
        }

        return $this->client->request($params);
    }

    /**
     * Claim $PayByName Tag
     *
     * @param $tagID - The tag's unique ID (obtained from 'get_pbn_list'.
     * @param $name - Name for the tag; for example a value of 'Apple' would be the PayByName tag $Apple. Make sure to use the case you want the tag displayed with.
     * @return null
     * @throws ApiException
     */
    public function claimTag($tagID, $name)
    {
        $params = [
            'cmd' => 'claim_pbn_tag',
            'tagid' => $tagID,
            'name' => $name
        ];

        return $this->client->request($params);
    }
}