<?php

function sendOtp($phoneNumber, $message, $expiry = 300)
{
    $url = 'https://sms.skyio.site/api/otp/send';
    $apiKey = 'LbxHuQ9zhAtWObFRA6kn0yZCggNKdKz1CeJPfho94fhA8wWwvZVASitNjmTp0dxw';

    $data = [
        'to' => $phoneNumber,
        'message' => $message,   // must include {{otp}} placeholder
        'expire' => $expiry,
        'mode' => 'devices'      // optional, default 'devices'
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return ['success' => false, 'error' => $err];
    } else {
        return json_decode($response, true);
    }
}

function verifyOtp($phoneNumber, $otp)
{
    $apiKey = 'LbxHuQ9zhAtWObFRA6kn0yZCggNKdKz1CeJPfho94fhA8wWwvZVASitNjmTp0dxw';
    $url = "https://sms.skyio.site/api/otp/verify?otp=" . urlencode($otp) . "&phone=" . urlencode($phoneNumber);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Accept' => 'application/json'
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return ['success' => false, 'error' => $err];
    } else {
        return json_decode($response, true);
    }
}
