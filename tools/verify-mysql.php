<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app=require dirname(__DIR__).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c=config('database.connections.mysql');
$pdo=new PDO("mysql:host={$c['host']};port={$c['port']};dbname=icc_backend_ddl;charset=utf8mb4",$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$count=(int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='icc_backend_ddl'")->fetchColumn();
if($count===0) {
    $pdo->exec(file_get_contents(base_path('sql/schema.mysql.sql')));
    $pdo->exec(file_get_contents(base_path('sql/seed.mysql.sql')));
}
$tables=['users','makers','assets','characters','asset_character','posts','comments','likes'];
$counts=[]; foreach($tables as $t) $counts[$t]=(int)$pdo->query("SELECT count(*) FROM $t")->fetchColumn();
$report=['server'=>$pdo->query('select version()')->fetchColumn(),'database'=>'icc_backend_ddl','DDL'=>'executed','rows'=>$counts];
file_put_contents(base_path('docs/mysql-ddl-check.json'),json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
$queries=preg_replace('/--[^\n]*/','',file_get_contents(base_path('sql/joins.sql')));
$results="# SELECT с JOIN — MySQL 8.4.8\n\nБаза icc_backend_ddl создана SQL-скриптом schema.mysql.sql и заполнена seed.mysql.sql.\n";
foreach(array_filter(array_map('trim',explode(';',$queries))) as $i=>$sql) {
    $rows=$pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $results.="\n## Запрос ".($i+1)."\n\n~~~sql\n$sql;\n~~~\n\n";
    if($rows) {
        $keys=array_keys($rows[0]); $results.='| '.implode(' | ',$keys)." |\n|".implode('|',array_fill(0,count($keys),'---'))."|\n";
        foreach($rows as $row) $results.='| '.implode(' | ',array_map(fn($v)=>str_replace(["|","\n"],["/"," "],(string)$v),$row))." |\n";
    }
}
file_put_contents(base_path('docs/join-results.md'),$results);
$metadata=\Illuminate\Support\Facades\DB::select("SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_KEY,COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,ORDINAL_POSITION");
file_put_contents(base_path('docs/mysql-columns.json'),json_encode($metadata,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
