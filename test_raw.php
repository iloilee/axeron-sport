<?php
$ch = curl_init('http://localhost/axeron-sport-website-master/api/chatbot.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => 'Giày chạy bộ nào đang bán chạy nhất bên mình?']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
echo "Raw response:\n";
var_dump($res);
