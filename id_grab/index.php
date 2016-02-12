<?php

/**
 * API Helper for GDI HQ
 * Kristen Curtze - Chapter Leader @ GDI Rochester
 */

require '../config.php';
require '../meetup.php';

if(!empty($_POST['chapter_url'])){
    $groupID = null;
    $error = false;
    //get URL from form
    $gdiChapter = $_POST['chapter_url'];

    //explode full URL to just ending
    $gdiChapter = explode("meetup.com/",$gdiChapter);
    
    //clean extra slashes
    $gdiChapter = str_replace("/","",$gdiChapter[1]);
  
    
    try
    {
       $meetup = new Meetup(array('key'=>$MEETUP_KEY));
       $results = $meetup->getGroups(array('group_urlname'=>$gdiChapter));
       foreach($results->results as $meet){
        $groupID = $meet->id;
       }
    }
    catch(Exception $e)
    {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>GDI Meetup ID Grabber!</title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile Specific Metas
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">

  <!-- CSS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="stylesheet" href="../css/normalize.css">
  <link rel="stylesheet" href="../css/skeleton.css">

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script>
    $(document).ready(function(){

        $("#id_grabber").submit(function(){
            $('#loader').show();
        });
    });
  </script>
</head>
<body>
<a href="/api"><< Back</a>
  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <div class="container">
    <div class="row">
      <div class="six columns" style="margin-top: 25%">
        <h4>GDI Meetup ID Grabber :)</h4>
        <form action="" method="POST" id="id_grabber">
        <label for="chapter_url">Chapter Meetup URL:</label>
        <input class="u-full-width" placeholder="http://www.meetup.com/Girl-Develop-It-Rochester/" type="text" name="chapter_url" id="chapter_url" />
        <input type="submit" id="submit_ID" value="Grab ID" />
        </form>
        <br/><br/>
        <img src="../images/ajax_loader.gif" id="loader" style="display:none;" alt="loader" />
        <?php 
        if(isset($groupID)){
            echo "<h4>Meetup ID: ".$groupID."</h4>";
        }
        if($error){
            echo "<h4>Error: ".$error."</h4>";
        }?>
      </div>
    </div>
  </div>

<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
</body>
</html>