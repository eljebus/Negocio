<HTML>
<HEAD>
    <TITLE>Image Preview</TITLE>
    <script type='text/javascript'>
        var NS = (navigator.appName=="Netscape")?true:false;
        function fitPic()
        {
            iWidth = (NS)?window.innerWidth:document.body.clientWidth;
            iHeight = (NS)?window.innerHeight:document.body.clientHeight;
            iWidth = document.images[0].width - iWidth;
            iHeight = document.images[0].height - iHeight;
            window.resizeBy(iWidth + 16, iHeight + 16);
            self.focus();
            setTimeout('fitPic()', 250);
        }
    </script>
    <style>
        body { margin: 0; padding: 0; background: white; }
    </style>
</HEAD>
<body onload="fitPic()"> <img src='{$config_http_script_dir}/uploads/{$image}' border=0> </body>
</HTML>