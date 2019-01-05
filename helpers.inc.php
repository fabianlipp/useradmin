<?php

function echoJsonDataAsScript($id, $data) {
  echo '<script type="application/json" json-data id="' . $id . '">' . "\n";
  echo json_encode($data, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT) . "\n";
  echo '</script>' . "\n";
}

?>
