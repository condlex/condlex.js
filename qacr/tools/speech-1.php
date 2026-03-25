<?php
/*
 * ===___=====_===___=___===
 *   / _ \   /_\ / __| _ \
 *  | (_) | / _ \ (__|   /
 * ==\__\_\/_/=\_\___|_|_\==
 *  Q/A Conditioned Reflex
 * =========================
 *
 * Copyright @ 2026 Dinh Thoai Tran
 * <zinospetrel@sdf.org>
 *
 * QACR is distributed under GPL v2 license as
 * in ../LICENSE.js file. Please visit
 * [ https://github.com/condlex ] for more details.
 *
 */

global $qacr_config;
require_once('./config.php');

$data_dir = $qacr_config['qas_path'];

$json_file = $data_dir . '/qa.json';

$json_str = file_get_contents($json_file);
$json = json_decode($json_str, true);

$no = 1;
foreach ($json['qas'] as $item) {
  $i = $item['i'];
  $qf = "q_". $i . "_0_0.mp3";
  $af = "a_" . $i . "_0.mp3";
  $fd = $data_dir . "/" . $i;
  $q = $item['qs'][0];
  $a = $item['a'];
  $no++;

  shell_exec("mkdir -p " . $fd);

  $cmd = 'rm -rf ' . $fd . "/" . str_replace('.mp3', '*.mp3', $qf);
  shell_exec($cmd);
  $cmd = 'rm -rf ' . $fd . "/" . str_replace('.mp3', '*.mp3', $af);
  shell_exec($cmd);

  $cmd = 'cp -rf "' . $data_dir . '/index.html" "' . $fd . '/"';
  shell_exec($cmd);

  ltts($q, realpath($fd) . "/" . $qf, 0, "en-US");
  ltts($a, realpath($fd) . "/" . $af, 0, "en-US");

  $qf = "q_". $i . "_0_1.mp3";
  $af = "a_" . $i . "_1.mp3";

  ltts($q, realpath($fd) . "/" . $qf, 1, "en-GB");
  ltts($a, realpath($fd) . "/" . $af, 1, "en-GB");

  $qf = "q_". $i . "_0_2.mp3";
  $af = "a_" . $i . "_2.mp3";

  ltts($q, realpath($fd) . "/" . $qf, 2, "en-AU");
  ltts($a, realpath($fd) . "/" . $af, 2, "en-AU");

  echo "==> Q/A " . $i . " is completed ...\n";
}

function ltts($text, $filename, $vi = 0, $vc = "en-US") {
  $list = [];
  $fld = explode(".", $text);
  foreach ($fld as $tx) {
    if (strlen($tx) == 0) continue;
    $tx .= ".";
    if (strlen($tx) > 200) {
      $pos2 = strpos($tx, " ", 150);
      if ($pos2 !== false) {
        array_push($list, substr($tx, 0, $pos2));
        array_push($list, substr($tx, $pos2 + 1));
      }
    } else {
      array_push($list, $tx);
    }
  }
  if (count($list) > 1) {
    print_r($list);
    for ($i = 0; $i < count($list); $i++) {
      tts($list[$i], str_replace(".mp3", "_" . ($i + 1) . ".mp3", $filename), $vi, $vc);
    }
  } else {
    tts($text, $filename, $vi, $vc);
  }
}

function tts($text, $filename, $vi = 0, $vc = "en-US") {
  $data = '{"engine": "Google", "data": {"text": "' . str_replace("'", "`", str_replace('"', '`', $text)) . '", "voice": "' . $vc . '"}}';
  $cmd = 'curl -X POST https://api.soundoftext.com/sounds -H "Content-Type: application/json" -d ' . "'" . $data . "'";
  $json_str = shell_exec($cmd);
  $json = json_decode($json_str, true);
  if (!$json['success']) return;
  $id = $json['id'];
  $url = "https://api.soundoftext.com/sounds/" . $id;
  $json = json_decode(file_get_contents($url), true);
  print_r($json);
  while ($json['status'] != 'Done') {
    sleep(1);
    $json = json_decode(file_get_contents($url), true);
    print_r($json);
  }
  $url = $json['location'];
  $cmd = 'curl -o "' . $filename . '" "' . $url . '"';
  $rs = shell_exec($cmd);
  echo $rs . "\n";
}
?>
