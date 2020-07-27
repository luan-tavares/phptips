<?php
  $v->layout("root::template", ["title"=>"Oi eu sou o Goku"]);
?>
<?= session()->flash(); ?>
<?= $v->start("footer"); ?>
footer dentro de main
<?= $v->stop(); ?>
dasdsa