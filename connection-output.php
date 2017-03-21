<?php

function simpleXOR($input, $key = false) {
    // append key on itself until it is longer than the input
    while (strlen($key) < strlen($input)) { $key .= $key; }

    // trim key to the length of the input
    $key = substr($key, 0, strlen($input));

    // Simple XOR'ing, each input byte with each key byte.
    $result = '';
    for ($i = 0; $i < strlen($input); $i++) {
        $result .= $input{$i} ^ $key{$i};
    }
    return $result;
}

$dsn = "mysql:host=localhost;dbname=march;charset=utf8";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, "root", "", $opt);

$need_upgrade = [];

$stmt = $pdo->query('select * from system_connections where type = "com.amazon" and user_id = 219144');
while ($row = $stmt->fetch())
{

$data = json_decode(
    simpleXOR(base64_decode($row['data']), "700f72107cc1112cdcdb4e923c34792b"), true
);

    if (empty($data['bucket_region'])) {
        $need_upgrade[$row['id']] = ['bucket'=>$data['bucket'], 'user_id'=>$row['user_id']];
    }
}
echo "\n";

echo json_encode($need_upgrade);