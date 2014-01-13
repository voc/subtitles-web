<!DOCTYPE>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>30C3 Subtitles</title>
	<meta charset="utf-8">
</head>
<body>
<pre>

{| class="wiki sortable" border="1"
! VID
! title
! pad
! amara
! status orig
! status other
! comments
<?php foreach ($talks as $talk):
  if (in_array($talk['fahrplan_id'], $blacklist)) {
    continue;
  } ?>

|-
| <?= h($talk['fahrplan_id']); 
?>

| <?= h($talk['title']); 
?>

| [http://subtitles.pads.ccc.de/<?= h($talk['fahrplan_id']); ?> Pad]
| <?php if (isset($amara[$talk['fahrplan_id']])): ?>[<?php echo $amara[$talk['fahrplan_id']]; ?> Video on amara.org]<?php else: ?>–<?php endif; ?>

| <?php 
$i = 0;
if (isset($subtitles[$talk['fahrplan_id']])){ 
    foreach ($subtitles[$talk['fahrplan_id']] as $language => $complete) { 
      $i++;
      if ($i==1) 
        echo (($complete)? 'complete' : 'text at amara') . " (" . $language . ") \n| ";
      else {
        echo $language . (($complete)? '✓' : '✗') . " ";
        //if ($i>=2) echo "&lt;br/&gt;";
      } 
    
  }
}
else echo "unknown";

if ($i< 1) echo "\n| ";

endforeach;

?>

|}
</pre>
</body>
</html>
