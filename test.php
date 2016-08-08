<?php
require_once 'vendor/autoload.php';

use uploader\lib\Uploader;

if (isset($_FILES['ImgFile'])) {
    
    $uploader = new Uploader();
    
    $allow_ext = array(
        'gif',
        'png',
        'jpg',
        'jpeg'
    );
    $input_name = "ImgFile";
    $path = "/pic/";
    
    $uploader->setParams($path, $input_name, $allow_ext);
    
    $file_name = $uploader->uploadFile();
    
    if ($file_name)
        echo "<div >Name: " . $file_name . "</div>";
}


if (isset($_POST['ImgFile2'])) {

    $uploader = new Uploader();

    $allow_ext = array(
        'gif',
        'png',
        'jpg',
        'jpeg'
    );
    $input_name = "ImgFile";
    $path = "/pic/";

    $uploader->setParams($path, $input_name, $allow_ext);

    $url = filter_var($_POST['ImgFile2'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
    
    $file_name = $uploader->downloadFile($url);

    if ($file_name)
        echo "<div >Name: " . $file_name . "</div>";
}

?>

<style>
div {
	margin: 30px
}
</style>

<div>

	<form action="test.php" method="post" enctype="multipart/form-data">

		<input type="FILE" name="ImgFile" /> <input type="submit"
			value="upload" />

	</form>

</div>

<div>

	<form action="test.php" method="post" enctype="multipart/form-data">

		URL: <input type="text" name="ImgFile2" /> 
		<input type="submit" value="download" />

	</form>

</div>

<script>



</script>
