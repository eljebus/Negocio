<?php

    error_reporting(E_ALL ^ E_NOTICE);

?>
<html>
<head><title>Example1</title></head>
<body>

<a href="<?php echo $_SERVER['PHP_SELF']; ?>?go=news">news</a> ||
<a href="<?php echo $_SERVER['PHP_SELF']; ?>?go=headlines">headlines</a> ||
<a href="<?php echo $_SERVER['PHP_SELF']; ?>?go=archives">archives</a> ||
<a href="<?php echo $_SERVER['PHP_SELF']; ?>?go=search">search</a> ||
<a href="<?php echo $_SERVER['PHP_SELF']; ?>?go=userlist">userlist</a> ||
<a style="font-size:120%" href="example2.php">See Advanced Example >></a>

<hr>


<?PHP
error_reporting (E_ALL ^ E_NOTICE);

if($_GET['go'] == "" or $_GET['go'] == "news"){
   include("show_news.php");
}
elseif($_GET['go'] == "headlines"){
   $template = "Headlines";
   include("show_news.php");
}
elseif($_GET['go'] == "archives"){
   include("show_archives.php");
}
elseif($_GET['go'] == "search"){
   include("search.php");
}
elseif($_GET['go'] == "userlist"){
    $imod = 'userlist';
    $user_flags = 'unraple';
    include("shows.php");
}
?>

</body>
</html>