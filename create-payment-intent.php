<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $input['amount'],
        'currency' => $input['currency'],
        'automatic_payment_methods' => [
            'enabled' => true,
        ],
    ]);

    echo json_encode(['clientSecret' => $paymentIntent->client_secret]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}