<h1>All Variables You are Allowed to Use</h1>
Here is a list of all possible variables that you are allowed to use when including news or archives:<BR>
<div class="code" style='font-family: Verdana, Arial, Helvetica, sans-serif;'>&lt;?PHP<br><br>
    <b>$number = "&lt;X&gt;";</b>&nbsp;&nbsp;<i>// show only the X newest articles.</i><br>
    <b>$template = "&lt;NAME&gt;";</b>&nbsp;&nbsp;<i>// load another template, if you don't use it the default template will be loaded.</i><br>
    <b>$static = TRUE;</b>&nbsp;&nbsp;<i>// included news will not load on this location, for more info see 'Using Multiple Includes'.</i><br>
    <b>$category = "&lt;ID&gt;";</b>&nbsp;&nbsp;<i>// show only news from the selected category where &lt;ID&gt; is the id of category.</i><br>
    <b>$start_from = "&lt;NUMBER&gt;";</b>&nbsp;&nbsp;<i>// show the news starting not from the first newest but from &lt;NUMBER&gt;, it is required to use $start_from = "&lt;NUMBER&gt;";
        only when you use $number = "X";.</i><br>

    <br>include("path/to/show_news.php");<br>
    ?&gt;</div>
All of the above variables are optional and you may wish don't to use them.