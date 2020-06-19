<?php
date_default_timezone_set("Asia/Ho_Chi_Minh");
session_start();  
function loginForm(){
    echo'
    <div id="loginform">
    <form action="index.php" method="post">
  <div class="form-group row">
    <label for="inputEmail3" class="col-sm-2 col-form-label">NAME:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="name" name="name" placeholder="user name">
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-10">
      <button type="submit" class="btn btn-primary" name="enter" id="enter">Sign in</button>
    </div>
  </div>
</form>
</div>
    ';
        // <div id="loginform">
    // <form action="index.php" method="post">
    //     <p>Please enter your name to continue:</p>
    //     <label for="name">Name:</label>
    //     <input type="text" name="name" id="name" />
    //     <input type="submit" name="enter" id="enter" value="Enter" />
    // </form>
    // </div>
}
if(isset($_POST['enter'])){
    if($_POST['name'] != ""){
        $_SESSION['name'] = stripslashes(htmlspecialchars($_POST['name']));
    }
    else{
        echo '<span class="error">Please type in a name</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./style/style.css" type="text/css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
<script type="text/javascript">
// jQuery Document
$(document).ready(function(){
	//If user wants to end session
	$("#exit").click(function(){
		var exit = confirm("Are you sure you want to end the session?");
		if(exit==true){window.location = 'index.php?logout=true';}		
        <?php
            if(isset($_GET['logout'])){ 
            //Simple exit message
            $fp = fopen("log.html", 'a');
            fwrite($fp, "<div class='msgln'><i>(".date("g:i A").")User <strong>". $_SESSION['name'] ."</strong> has left the session.</i><br></div>");
            fclose($fp);
            session_unset();
            header("Location: index.php"); //Redirect the user
            }
        ?>
	});
    //show file name in file browers
    $('.custom-file-input').change(function (e) {
        var files = [];
        for (var i = 0; i < $(this)[0].files.length; i++) {
            files.push($(this)[0].files[i].name);
        }
        $(this).next('.custom-file-label').html(files.join(', '));
    });

    //Auto-scroll			
    	//Load the file containing the chat log

				
				//Auto-scroll			
				var newscrollHeight = $("#chatbox").attr("scrollHeight") - 20; //Scroll height after the request
				$("#chatbox").animate({ scrollTop: newscrollHeight }, 'normal'); //Autoscroll to bottom of div
		
});
</script>
</head>
<body>
<?php
if(!isset($_SESSION['name'])){
    loginForm();
}
else{

function encrypt_file($file, $key) {
    //get content of file under string
$contents = file_get_contents($file);
    // Generate an initialization vector
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    // Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
$encrypted = openssl_encrypt($contents, 'aes-256-cbc', $key, 0, $iv);     
    // Write content in the destination file.
    // Open the file and returns a file pointer resource. 
$handle = fopen($file,'wb') or die("Could not open a file.");
    //write encrypted into file
fwrite($handle, base64_encode($encrypted . '::' . $iv)) or die("Could not write to file."); 
   // Close the opened file pointer.
fclose($handle); 
}
function decrypt_file($file,$key){
    //get content of file under string
    $contents = file_get_contents($file);
    //split into encrypt and iv    
    list($encrypted_file,$iv) = explode("::", base64_decode($contents),2);
    //decrypted
    $decrypted = openssl_decrypt($encrypted_file,'aes-256-cbc',$key,0,$iv);
    //open file to write
    $handle = fopen($file,'w+b') or die('Could not open a file');
    //write plaintext into file
    fwrite($handle,$decrypted) or die("Could not write to file");
    //close file
    fclose($handle);
}
 
function read_key($file){
    $handle = @fopen($file,'rb') or die("Could not open a file");
    $contents = @fread($handle,filesize($file));
    return $contents;
}

function hash_result($file){
  $contents = file_get_contents($file);
  return md5($contents);
}

//encrypt
if(isset($_POST['submit']) && isset($_FILES['file'])&& isset($_FILES['key']) ){

$filepath = $_FILES['file']['tmp_name'];
$filename = $_FILES['file']['name'];
$keypath = $_FILES['key']['tmp_name'];
$key = read_key($keypath);
//move file upload to server
move_uploaded_file($filepath,'server/'.$filename);
//create backup file upload
copy('server/'.$filename,'plaintext/'.$filename);
//encypt file input
encrypt_file('server/'.$filename,$key);
//show notification that file uploaded
$handle = fopen("log.html", 'a');
fwrite($handle, "<div class='msgln'><i>(".date("g:i A").")User <strong>". $_SESSION['name'] ."</strong> uploaded file:<strong>".$filename   ."</strong> to server.</i><br></div>");
fclose($handle);
}
//decrypt
else if(isset($_POST['outsubmit']) && isset($_FILES['defile']) &&  isset($_FILES['dekey'])){
    $keypath = $_FILES['dekey']['tmp_name'];
    $key = read_key($keypath);
    $defilepath = $_FILES['defile']['tmp_name'];
    $defilename = $_FILES['defile']['name'];
    move_uploaded_file($defilepath,'encrypt/'.$defilename);
    decrypt_file('encrypt/'.$defilename,$key);
    $handle = fopen("log.html", 'a');
    fwrite($handle, "<div class='msgln'><i>(".date("g:i A").")User<strong> ". $_SESSION['name'] ."</strong> uploaded file:<strong>".$defilename   ."</strong> to encrypt.</i><br></div>");
    fclose($handle);
    }
else if(isset($_POST['hsubmit'])&&isset($_FILES['plaintext'])&&isset($_FILES['encrypt'])){
  $plain_filename = $_FILES['plaintext']['name'];
  $en_filename = $_FILES['encrypt']['name'];
  $hash_plaintext = hash_result("plaintext/".$plain_filename);
  $hash_encrypt = hash_result("encrypt/".$en_filename);
  $handle = fopen("log.html", 'a');
  fwrite($handle, "<div class='msgln'><i>(".date("g:i A").")User<strong> ". $_SESSION['name'] ."</strong>: <br> hash value of plaintext: ".$hash_plaintext ."</i><br>hash value after decrypt: ".$hash_encrypt ."</div>");
  fclose($handle);

  
}
?>
<div id="wrapper">
    <div id="menu">
        <p class="welcome">Welcome, <b><?php echo $_SESSION['name']; ?></b></p>
        <p class="logout"><a href="#" class="badge badge-primary" id="exit">LOGOUT</a></p>
        <div style="clear:both"></div>
    </div>    
    <div id="chatbox"><?php
if(file_exists("log.html") && filesize("log.html") > 0){
    $handle = fopen("log.html", "r");
    $contents = fread($handle, filesize("log.html"));
    fclose($handle);
     
    echo $contents;
    
}
?></div>
<div class="container">
<form name="message" action="" enctype="multipart/form-data" method="post">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">PLAINTEXT FILE</span>
  </div>
  <div class="custom-file">
    <input type="file" class="custom-file-input" id="file" name="file">
    <label class="custom-file-label" for="file">Choose file</label>
  </div>
</div>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">KEY FILE</span>
  </div>
  <div class="custom-file">
    <input type="file" class="custom-file-input" id="key" name="key">
    <label class="custom-file-label" for="key">Choose file</label>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-sm">
    </div>
    <div class="col-sm" id="button">
    <button type="submit" name="submit" id="submit" class="btn btn-secondary">ENCRYPT</button>
    </div>
    <div class="col-sm">
    </div>
  </div>
</div>
</div>
    
<div>------------------------------------------------------------------------------</div>

<div class="container">
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">ENCRYPTED FILE</span>
  </div>
  <div class="custom-file">
    <input type="file" class="custom-file-input" id="defile" name="defile">
    <label class="custom-file-label" for="file">Choose file</label>
  </div>
</div>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">KEY FILE</span>
  </div>
  <div class="custom-file">
    <input type="file" class="custom-file-input" id="dekey" name="dekey">
    <label class="custom-file-label" for="key">Choose file</label>
  </div>
</div>
</div>


<div class="container">
  <div class="row">
    <div class="col-sm">
    </div>
    <div class="col-sm" id="button">
    <button type="submit" name="outsubmit" id="outsubmit" class="btn btn-secondary">DECRYPT</button>
    </div>
    <div class="col-sm">
    </div>
  </div>
  <div>-------------------------------------------------------------------------</div>
  <div>----------------------------HASH CHECKING--------------------------</div>
  <div>-------------------------------------------------------------------------</div>
  <form name="message" action="" enctype="multipart/form-data" method="post">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">PLAINTEXT FILE</span>
  </div>
  <div class="custom-file">
    <input type="file" class="custom-file-input" id="plaintext" name="plaintext">
    <label class="custom-file-label" for="file">Choose file</label>
  </div>
</div>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">ENCRYPTED FILE</span>
  </div>
  <div class="custom-file">
    <input type="file" class="custom-file-input" id="encrypt" name="encrypt">
    <label class="custom-file-label" for="encrypted">Choose file</label>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-sm">
    </div>
    <div class="col-sm" id="button">
    <button type="submit" name="hsubmit" id="hsubmit" class="btn btn-secondary">HASH</button>
    </div>
    <div class="col-sm">
    </div>
  </div>
</div>
</form>
</div>
<?php
}
?>
</body>
</html>




