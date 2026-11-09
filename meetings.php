<?php
// Stockage des réunions (ordre du jour, commentaires, statut) dans data/meetings.json
// GET  -> toutes les réunions {id: {...}}
// POST {meeting:{id,...}} -> crée/met à jour ; POST {delete:id} -> supprime
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$dir = __DIR__.'/data';
if (!is_dir($dir)) @mkdir($dir, 0750, true);
$fp = fopen($dir.'/meetings.json', 'c+');
if (!$fp) { http_response_code(500); echo '{"error":"stockage indisponible"}'; exit; }
flock($fp, LOCK_EX);
$d = json_decode(stream_get_contents($fp), true) ?: [];
$dirty = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $in = json_decode(file_get_contents('php://input'), true) ?: [];
  if (!empty($in['delete'])) {
    $id = preg_replace('/[^a-zA-Z0-9\-]/', '', (string)$in['delete']);
    unset($d[$id]); $dirty = true;
  } elseif (isset($in['meeting']['id'])) {
    $m  = $in['meeting'];
    $id = preg_replace('/[^a-zA-Z0-9\-]/', '', (string)$m['id']);
    if ($id !== '' && strlen(json_encode($m)) < 300000) {
      $m['id'] = $id; $m['updated_at'] = date('c');
      $d[$id] = $m; $dirty = true;
    }
  }
  if ($dirty) { ftruncate($fp, 0); rewind($fp); fwrite($fp, json_encode($d, JSON_UNESCAPED_UNICODE)); }
  flock($fp, LOCK_UN); fclose($fp);
  echo '{"ok":true}'; exit;
}
flock($fp, LOCK_UN); fclose($fp);
echo $d ? json_encode($d, JSON_UNESCAPED_UNICODE) : '{}';
