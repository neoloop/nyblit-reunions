<?php
// Proxy Nyblit : usage  proxy.php?p=/tasks&limit=50
$cfg = require __DIR__.'/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$path = $_GET['p'] ?? '';
if (!preg_match('#^/(me|dispositions|tasks|projects|workflows)(/[A-Za-z0-9_\-/]*)?$#', $path)) {
  http_response_code(400); echo json_encode(['title'=>'Chemin refusé','status'=>400]); exit;
}
$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['GET','POST','PATCH','DELETE'], true)) { http_response_code(405); exit; }

$q = $_GET; unset($q['p']);
$url = rtrim($cfg['base_url'], '/').$path.($q ? '?'.http_build_query($q) : '');

$headers = ['Authorization: Bearer '.$cfg['token'], 'Accept: application/json'];
foreach (['HTTP_IF_MATCH'=>'If-Match','HTTP_IDEMPOTENCY_KEY'=>'Idempotency-Key'] as $k=>$h)
  if (!empty($_SERVER[$k])) $headers[] = "$h: ".$_SERVER[$k];
$body = file_get_contents('php://input');
if ($body !== '' && $body !== false) $headers[] = 'Content-Type: application/json';

$respHeaders = [];
$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_CUSTOMREQUEST => $method,
  CURLOPT_HTTPHEADER => $headers,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 20,
  CURLOPT_HEADERFUNCTION => function($c, $line) use (&$respHeaders) {
    $p = explode(':', $line, 2);
    if (count($p) === 2) $respHeaders[strtolower(trim($p[0]))] = trim($p[1]);
    return strlen($line);
  },
]);
if ($body !== '' && $body !== false) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
$out = curl_exec($ch);
if ($out === false) { http_response_code(502); echo json_encode(['title'=>curl_error($ch),'status'=>502]); exit; }
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($code);
foreach (['etag','ratelimit-remaining','ratelimit-reset','retry-after'] as $h)
  if (isset($respHeaders[$h])) header('X-Nyb-'.$h.': '.$respHeaders[$h]);
echo $out === '' ? '{}' : $out;
