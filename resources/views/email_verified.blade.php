<html>
<head>
    <title>Email verified</title>
</head>
<body>
    <center>Your email has been verified</center>
    <center><div></div></center>
</body>
<script>
    setTimeout(() => {
        window.location = "<?=  env('APP_URL_WEBSITE') ?>"
    }, 1000);
</script>
</html>