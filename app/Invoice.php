<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Invoice extends Model {

    protected $connection = 'mongodb';
    protected $collection = 'invoices';

    protected $fillable = [
        'user', 'sum', 'currency', 'id', 'confirmations', 'status', 'data', 'type', 'aggregator', 'internal_id', 'redirected', 'ledger', 'min_deposit', 'payid', 'min_deposit_usd', 'hash', 'pull_state', 'pull_txid', 'usd'
    ];

}
