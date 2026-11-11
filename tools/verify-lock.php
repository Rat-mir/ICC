<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app=require dirname(__DIR__).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c=config('database.connections.mysql');
$connect=fn()=>new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$a=$connect();$b=$connect();
$id=$a->query('SELECT id FROM makers ORDER BY id LIMIT 1')->fetchColumn();
$before=$a->query("SELECT title FROM makers WHERE id=$id")->fetchColumn();
$a->beginTransaction();$a->query("SELECT * FROM makers WHERE id=$id FOR UPDATE")->fetch();
$b->exec('SET innodb_lock_wait_timeout=1');$b->beginTransaction();
$blocked=false;
try {$b->exec("UPDATE makers SET title='must-not-be-committed' WHERE id=$id");}
catch(PDOException $e) {$blocked=(int)($e->errorInfo[1]??0)===1205;}
finally {$b->rollBack();$a->rollBack();}
$after=$a->query("SELECT title FROM makers WHERE id=$id")->fetchColumn();
if(!$blocked || $before!==$after) throw new RuntimeException('Lock or rollback verification failed.');
$result=['engine'=>'MySQL 8.4 InnoDB','connections'=>2,'lock_wait_timeout'=>1205,'blocked_concurrent_update'=>true,'rollback_preserved_data'=>true];
file_put_contents(base_path('docs/mysql-lock-check.json'),json_encode($result,JSON_PRETTY_PRINT));
echo json_encode($result,JSON_PRETTY_PRINT)."\n";
