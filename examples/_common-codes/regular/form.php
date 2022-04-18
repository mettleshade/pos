<?php

require_once '_config.php';

$order = getNewOrder($baseUrl, $ip, $request->get('currency', 'TRY'), $session, $request->get('installment'));
$session->set('order', $order);
$transaction = $request->get('tx', \Mews\Pos\Gateways\AbstractGateway::TX_PAY);

$card = createCard($pos, $request->request->all());

require '../../template/_payment_response.php';