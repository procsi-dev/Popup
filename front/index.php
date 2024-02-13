 <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>GLPI</title>
    <script type="text/javascript">

        function $_GET(param) {
            var vars = {};
            window.location.href.replace( location.hash, '' ).replace(
                /[?&]+([^=&]+)=?([^&]*)?/gi,
                function( m, key, value ) {
                    vars[key] = value !== undefined ? value : '';
                }
            );

            if ( param ) {
                return vars[param] ? vars[param] : null;
            }
            return vars;
        }



        function popup() {
            var deco_var = decodeURI( $_GET( 'num' ) );
            user_url = 'page.php?num=' + deco_var;

            if(deco_var !== "null"){
                window.open(user_url,"pub","toolbar=yes,location=yes,directories=no,menubar=no,scrollbars=yes,status=yes,resizable=1,width=800, height=800").focus();
                parent.close();
            }
        }
    </script>

</head>
<body onload="popup()">
</body>
</html>
