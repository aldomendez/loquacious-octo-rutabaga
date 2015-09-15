<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Oven Loader</title>
  <link rel="stylesheet" href="./semanticUi/css/semantic.css" media="screen">
  <link rel="stylesheet" href="./PrintLib.css" media="print">
<style>
.ui.items>.item{
    min-height: 80px;
}

@media screen {
	.hide{
		display: none;
	}
}
</style>
</head>
<body>

<div class="ui column page grid" id="output">
  <!-- Aqui se inserta el template -->
</div>

<script id="template" type="text/MaxTemplate">
  <?php include 'template.php'; ?>
</script>

<script src="vendor/jquery-2.1.0.min.js"></script>
<script src="vendor/underscore-1.5.2.min.js"></script>
<script src="vendor/Ractive.js"></script>
<!-- <script src="vendor/cenny.js"></script> -->
<script src="js/oven.js"></script>
</body>
</html>