<html><head><title>submitted</title></head>
<body>
<pre>
<?php echo htmlspecialchars(print_r($_POST, true)); ?>
</pre><br />
<pre>
<?php printf("0x%08x",crc32($_POST['hc_collision'])); ?>
</pre>
</body>
</html>
