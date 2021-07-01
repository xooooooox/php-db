## Installation
```bash
composer require xooooooox/db
``` 

## For example

```php
<?php

use xooooooox\db\ObjPdo;

$dsn = 'mysql:dbname=test;host=127.0.0.1';
$user = 'root';
$pass = '';
$options = [];
$db = new ObjPdo($dsn,$user,$pass,$options);

// query sql
$users = $db->query('SELECT * FROM `user` ORDER BY `id` DESC LIMIT 0, 10;');
var_dump($users);

// execute transaction
$start = microtime(true);
$err = '';
$result = $db->transaction(function(ObjPdo $p){
    $times = (string)time();
    $params = [$times,$times];
    $p->execute('INSERT INTO `user` (`nickname`, `mobile`) VALUES ( ?, ? );',$params);
    $add = $p->first('SELECT `id` FROM `user` WHERE (`nickname` = ? AND `mobile` = ?) ORDER BY `id` DESC LIMIT 0, 1;',$params);
    if (!isset($add['id'])){
        // insert data successfully, query unsuccessful, throw an exception, roll back the transaction
        throw new PDOException('user information is not found');
    }
    $p->execute('INSERT INTO `user2` (`nickname`, `mobile`) VALUES ( ?, ? );',$params);
    $params[] = (string)$add['id'];
    $p->execute('INSERT INTO `user3` (`nickname`, `mobile`, `invite_code`) VALUES ( ?, ?, ? );',$params);
},function(PDOException $e)use(&$err){
    $err = $e->getMessage();
});
$end = microtime(true);
$using = $end-$start;
printf("transaction time consuming: %f micro seconds\n",$using);
if (!$result){
    printf("transaction execution failed: %s\n",$err);
}else{
    printf("the transaction is executed successfully\n");
}

```
