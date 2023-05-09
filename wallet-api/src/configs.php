<?php

$settings = [
  // DATABASE SETTINGS
  'db' => [
    'host' => getenv('MYSQL_HOST'),
    'name' => getenv('MYSQL_DATABASE'),
    'user' => getenv('MYSQL_USER'),
    'pass' => getenv('MYSQL_PASSWORD'),    
  ],
  // JWT SETTINGS
  'jwt' => [
    'issuer'      => getenv('JWT_ISSUER'),
    'lifetime'    => getenv('JWT_LIFETIME'),
    'private_key' => getenv('JWT_PRIVATE_KEY'),
    'public_key'  => getenv('JWT_PUBLIC_KEY'),    
  ],
  // BTC SETTINGS
  'btc' => [
    'network' => getenv('BTC_NETWORK')
  ]
];

return json_decode(json_encode($settings), FALSE);