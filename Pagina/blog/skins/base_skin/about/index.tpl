<div style="margin: 0 32px 0 0;">

    <div style="background: #F7F6F4; padding: 4px; font: 15px/1em Arial;  margin: 0 0 4px 0;">Help &amp; Support</div>
    <div style="margin: 0 0 0 32px; font-size: 13px;">
        <p>
            If you face any problem the first thing you should do is to pay your attention to <a style="font-size: 13px;" href="{$PHP_SELF}?mod=help">CuteNews internal Help Documentation</a>.
            If it doesn't help, look at <a style="font-size: 13px;" href="http://cutephp.com/faq/" target="_blank">FAQ section</a> or visit our <a style="font-size: 13px;" href="http://cutephp.com/forum/" target="_blank">support
            forum</a>. Read <a target="_blank" style="font-size: 13px;"  href="README.html">README.html</a> file before it.
        </p>

        <ol>
            <li><a style="font-size: 12px;" href="{$PHP_SELF}?mod=help">Internal Help Documentation</a></li>
            <li><a style="font-size: 12px;" href="http://cutephp.com/cutenews/readme.html" target=_blank>Readme.html File (online)</a></li>
            <li><a style="font-size: 12px;" href="http://cutephp.com/forum/" target=_blank>Support Forums</a></li>
        </ol>

        <div><strong>License Version:</strong> <script type="text/javascript" src="http://cutephp.com/cutenews/check_version.php?mybid={$config_version_id}"></script></div>
        <br/>
        <div><strong>Important note:</strong> no formal official support is provided apart from the support forum found on this website and maintained by other users.</div>
    </div>

    <div style="height: 16px;"></div>
    
    {REG}
        <div style="background: #F7F6F4; padding: 4px; font: 15px/1em Arial;  margin: 0 0 4px 0;">Rate our Script</div>
        <form action="http://www.hotscripts.com/rate/21011.html" method="POST" target="_blank">
            <label for="rating">Please rate our script <a href="http://www.hotscripts.com/?RID=11206" style="text-decoration: none">@ HotScripts.com</a> if you like it. &nbsp;</label>
            <select id="rating" name=rating size=1>
                <option value=5 selected>Excellent!</option>
                <option value=4>Very Good</option>
                <option value=3>Good</option>
                <option value=2>Fair</option>
                <option value=1>Poor</option>
            </select>
            <input type="submit" value="Cast My Vote!">
            <input type="hidden" name="sexternal" value="1">
        </form>
    {/REG}

    {-REG}

        <div style="background: #F7F6F4; padding: 4px; font: 15px/1em Arial; margin: 0 0 4px 0;">Unregistered</div>
        <div><span style="color: red; font-size: 14px;">&raquo; &raquo; &raquo; Your version of CuteNews is Unregistered</span> -
             <b><a href="http://cutephp.com/cutenews/register/" target=_blank>Find out how to register it</a></b>
             by registering your version, all 'powered by CuteNews...' lines will be removed
        </div>

    {/-REG}

</div>
