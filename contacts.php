<?php
// Annuaire des collaborateurs (nom + e-mail), stocké dans data/contacts.json
// GET -> liste ; POST {contacts:[{id,name,email}]} -> remplace la liste
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$dir = __DIR__.'/data';
if (!is_dir($dir)) @mkdir($dir, 0750, true);
$fp = fopen($dir.'/contacts.json', 'c+');
if (!$fp) { http_response_code(500); echo '[]'; exit; }
flock($fp, LOCK_EX);
$d = json_decode(stream_get_contents($fp), true);
if (!is_array($d)) $d = [];
$cut = function ($s, $n) { return function_exists('mb_substr') ? mb_substr($s, 0, $n) : substr($s, 0, $n); };
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $in = json_decode(file_get_contents('php://input'), true);
  if (is_array($in['contacts'] ?? null)) {
    $out = [];
    foreach (array_slice($in['contacts'], 0, 500) as $c) {
      if (!is_array($c)) continue;
      $id    = preg_replace('/[^a-zA-Z0-9\-]/', '', (string)($c['id'] ?? ''));
      $name  = trim((string)preg_replace('/[\x00-\x1F]/', '', (string)($c['name'] ?? '')));
      $email = trim((string)preg_replace('/[\s\x00-\x1F]/', '', (string)($c['email'] ?? '')));
      if ($id === '' || $email === '') continue;
      $out[] = ['id' => $id, 'name' => $cut($name, 120), 'email' => $cut($email, 200)];
    }
    ftruncate($fp, 0); rewind($fp); fwrite($fp, json_encode($out, JSON_UNESCAPED_UNICODE));
  }
  flock($fp, LOCK_UN); fclose($fp);
  echo '{"ok":true}'; exit;
}
flock($fp, LOCK_UN); fclose($fp);
echo json_encode(array_values($d), JSON_UNESCAPED_UNICODE);
