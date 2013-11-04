<h1>Explaining archives and their Usage</h1>
When you send your news to the archive, CuteNews will automatically create a new archive file under the archives/ folder with extension .arch .
Then all news that you selected for archiving will be moved from news.txt (where only the active news are held) to the newly created file in archives/
Therefore the news you have archived won't be visible from show_news.php but from show_archives.php where all available archives are nicely listed.<br>
Once the news are archived CuteNews don't have built-in feature for moving back news from archive to active news, so the only way to do it is by manually
opening the archive file and copying its content to news.txt<br><br>
When you send all your active news to the archive there won't be left active news, but if you use<br> $number = <b>X</b>; in your include code, CuteNews will
automatically show the X newest news from the archive.<br><br>
Sending your news to archive is optional and you may never use it, but it is useful if you have many news articles and
want to organize them. Using archive is also recommended when you have more than 3000 active news.