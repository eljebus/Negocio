<html>
<head><style type="text/css">body { font-family: Arial, Tahoma, serif; }</style></head>
<body>
<h1 style="text-align: center; font-size: 20px; font-family: Arial, Tahoma, serif; cursor: pointer; color: #000080;" onclick="window.close();">OK</h1>
<?php

    $id = intval($_REQUEST['id']);
    $rate = intval($_REQUEST['rate']);

    if ($rate < 1) $rate = 1;
    elseif ($rate > 5) $rate = 5;

    $ndb = SERVDIR.'/cdata/news.txt';
    $news_db = implode('', file($ndb));

    if ( preg_match("~".$id."[^\n]+~", $news_db, $out) )
    {
         $a = explode('|', $out[0]);
         list ($r, $b) = explode('/', $a[NEW_RATE]);
         $a[NEW_RATE] = ($r + $rate).'/'.($b + 1);
         $rate = ($r + $rate) / ($b + 1);

         // save changes
         $fx = fopen($ndb, 'w');
         flock($fx, LOCK_EX); fwrite($fx, str_replace($out[0], implode('|', $a), $news_db)); flock($fx, LOCK_UN);
         fclose($fx);
    }

?>
<script type="text/javascript">
<?php
    for ($i = 1; $i <= 5; $i++)
        if ($rate < $i) { ?>window.opener.document.getElementById('<?php echo $id; ?>_<?php echo $i; ?>').innerHTML = '<?php echo RATEN_SYMBOL; ?>'; <?php }
                   else { ?>window.opener.document.getElementById('<?php echo $id; ?>_<?php echo $i; ?>').innerHTML = '<?php echo RATEY_SYMBOL; ?>'; <?php }
?>

</script>
</body></html>