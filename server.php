<?php
// Router for the PHP development server, independent of cwd and variables_order.
$public=__DIR__.'/public';
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH) ?: '/');
$candidate=realpath($public.$path);
if($path!=='/' && $candidate && str_starts_with($candidate,realpath($public).DIRECTORY_SEPARATOR) && is_file($candidate)) return false;
require $public.'/index.php';
