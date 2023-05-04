<!-- requirejs -->
<script type="text/javascript">
    // allow requirejs to lazy load Css
    if ((typeof(loadCss) === 'undefined') || (typeof(loadCss) !== 'function')) {
        var loadCss = function(url) {
            var link = document.createElement("link")
            link.type = "text/css";
            link.rel = "stylesheet";
            link.href = url;
            document.getElementsByTagName("head")[0].appendChild(link);
        }
    }
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/require.js/2.3.6/require.min.js" integrity="sha512-c3Nl8+7g4LMSTdrm621y7kf9v3SDPnhxLNhcjFJbKECVnmZHTdo+IRO05sNLTH/D3vA6u1X32ehoLC7WFVdheg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
