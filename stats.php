<?php
// Compteur d'envois en ordre du jour, par tâche (stocké dans data/stats.json)
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$dir = __DIR__.'/data';
if (!is_dir($dir)) @mkdir($dir, 0750, true);
$fp = fopen($dir.'/stats.json', 'c+');
if (!$fp) { http_response_code(500); echo '{"error":"stockage indisponible"}'; exit; }
flock($fp, LOCK_EX);
$d = json_decode(stream_get_contents($fp), true) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $in  = json_decode(file_get_contents('php://input'), true) ?: [];
  $aid = preg_replace('/[^a-zA-Z0-9\-]/', '', $in['agenda_id'] ?? '');
  $ids = $in['task_ids'] ?? [];
  if ($aid !== '' && is_array($ids)) {
    foreach ($ids as $id) {
      if (!is_string($id) || !preg_match('/^[A-Za-z0-9\-]{8,64}$/', $id)) continue;
      $e = $d[$id] ?? ['count' => 0, 'last' => null, 'agendas' => []];
      if (!in_array($aid, $e['agendas'], true)) {   // un même ordre du jour ne compte qu'une fois
        $e['agendas'][] = $aid;
        $e['agendas'] = array_slice($e['agendas'], -100);
        $e['count']++;
        $e['last'] = date('c');
      }
      $d[$id] = $e;
    }
    ftruncate($fp, 0); rewind($fp); fwrite($fp, json_encode($d));
  }
}
flock($fp, LOCK_UN); fclose($fp);
$out = [];
foreach ($d as $id => $e) $out[$id] = ['count' => $e['count'], 'last' => $e['last']];
echo $out ? json_encode($out) : '{}';
