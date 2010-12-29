<?php include "hashcash.php"; ?>
<html>
<head>
<title>hashcash test page</title>
<script type="text/javascript" src="crc32.js"></script>
<script type="text/javascript" src="hashcash.js"></script>
</head>
<body>
<form id="stampform" action="post_comment.php" method="post" onsubmit="hc_SpendHash()">
<?php hc_CreateStamp(); ?>
<input type="text" name="username"><br />
<textarea rows="10" cols="50" name="comment"></textarea><br />
<input type="text" name="timeout" id="timeout" size="80">
<input type="submit" value="Go!">
</form>
</body>
</html>