<?php

$templateTitle = 'Refund Order';
require '_config.php';
require '../../template/_header.php';

use Mews\Pos\Gateways\AbstractGateway;

$ord = $session->get('order') ? $session->get('order') : getNewOrder($baseUrl);

// Refund Order
$order = [
    'id'       => $ord['id'],
    'amount'   => $ord['amount'],
    'currency' => $ord['currency'],
];
$transaction = AbstractGateway::TX_REFUND;
$pos->prepare($order, $transaction);

$pos->refund();

$response = $pos->getResponse();

require '../../template/_simple_response_dump.php';
require '../../template/_footer.php';
