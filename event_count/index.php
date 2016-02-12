<?php

/**
 * API Helper for GDI HQ
 * Kristen Curtze - Chapter Leader @ GDI Rochester
 */

require '../config.php';
require '../meetup.php';

function loopThroughNext($meetup, $results,&$progressArray){

   foreach($results->results as $feed){
     // grab time and convert back to seconds from milliseconds
     $progressArray[] = array('date'=>date('m/d/Y',$feed->time/1000),'meetup_id'=>$feed->group->id, 'has_next'=>strlen($results->meta->next));
     
   }
   //See if there is more data ("next" is set) -- loop through again if so...
   if(strlen($results->meta->next) > 0){
      $nextSet = $meetup->getNext($results);
      loopThroughNext($meetup,$nextSet,$progressArray);
   }

}

if(!empty($_POST['meetup_ids'])){
    $groupID = null;
    $error = false;

    $gdiChapter = $_POST['meetup_ids'];
    $timeFrame = $_POST['meetup_range'];
    $finalArray = array();
    try
    {
       $meetup = new Meetup(array('key'=>$MEETUP_KEY));
       $results = $meetup->getEvents(array('group_id'=>$gdiChapter,'page'=>'200','status'=>'upcoming,past','time'=>$timeFrame));
       //Meetup has a limit so we need to loop through it to get all chapter info
       loopThroughNext($meetup, $results, $finalArray);

       //Pass back results to be parsed by jquery
       echo json_encode($finalArray);
    }
    catch(Exception $e)
    {
        $error = $e->getMessage();
    }
}else{
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>GDI Event Counter!</title>
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
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script>
    $(document).ready(function(){

      //GDI chapters json file snagged from GDI website (https://www.girldevelopit.com/chapters.json)
      //Last grabbed 2/12/2016
      var chaptersURL = "chapters.json";
		  var plots = [];

      $( "#from" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 3,
        onClose: function( selectedDate ) {
          $( "#to" ).datepicker( "option", "minDate", selectedDate );
        }
      });
      $( "#to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 3,
        onClose: function( selectedDate ) {
          $( "#from" ).datepicker( "option", "maxDate", selectedDate );
        }
      });

      $("#events_form_submit").click(function(e){

        e.preventDefault();
        cleanSlate();

        if($('#from').val() !== "" && $('#to').val() !== ""){
          
          var startRange = Date.parse($('#from').val()+" 00:00:00 GMT");
          var endRange = Date.parse($('#to').val()+" 00:00:00 GMT");
          var chapterString = "";
          //format for Meetup's API date range
          var meetup_range = startRange+","+endRange;

          $.getJSON(chaptersURL).done(function (chapters) {

          //create and prepare list to be populated after Meetup API call
          $.each(chapters, function (i, plot) {
            newobj = {
              value: plot.chapter,
              meetupURL: plot.meetup,
              meetupID: plot.meetup_id
            }

            $("#events_area").append("<div style='float:left;' id='chapter_"+plot.meetup_id+"' class='chapters'>"+plot.chapter+":&nbsp;</div><div id='events_"+plot.meetup_id+"' style='float:left;' class='events'></div><br/>");

            chapterString+=plot.meetup_id;

            if(i < (chapters.length - 1)){
              chapterString+=",";
            }
          })
        }).done(function () {
          //let the loading begin
          $('#loader').show();
          //make call with chapter Meetup IDs and date ange
          $.post( "", { meetup_ids: chapterString, meetup_range: meetup_range})
          .done(function( data ) {
            $('#loader').hide();
            var results = JSON.parse(data);
            //default to 0 events
            $('.events').html(0);
            //total event count
            $('#total_num').html(results.length);
            $('#total_events').fadeIn();
            //loop through chapter event results and assign within the list
            $.each(results, function (i, eve){
              //match results to individual meetup ids and update totals accordingly per chapter
              $('#events_'+eve.meetup_id).html(parseInt($('#events_'+eve.meetup_id).html())+1);

            });
          });
        });
      }else{
        //cheap error handling :P
        alert('Please fill out both dates!');
      }
      }); 
  		
    });

  function cleanSlate(){
    $('#events_area').html("&nbsp;");
    $('.events').html(0);
    $('#total_num').html(0);
  }
  </script>
</head>
<body>
<a href="/api"><< Home</a> 

  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <div class="container">
    <div class="row">
      <div class="five columns" style="margin-top: 35%">
        <h4>GDI Meetup Event Counter :)</h4>
       
        <form id="events_form">
        <input type="text" placeholder="from" id="from" name="from">
        <input type="text" id="to" placeholder="to" name="to">
        <br/>
        <input id="events_form_submit" type="submit" value="grab events">
        <img style="display:none;" id="loader" src="../images/ajax_loader.gif" alt="loader" />
        </form>
        
      </div>
      <div class="three columns" id="events_area">
      &nbsp;
      </div>
      <div class="four columns" id="total_events" style="margin-top: 35%">
       <h3>Total Events: <span id='total_num'>0</span></h3>
      </div>
    </div>
  </div>

<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
</body>
</html>
<?php } ?>