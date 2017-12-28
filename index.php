<?php
require "class.rgbcontrol.php";
$led = new LedControl(17,22,24); # GPIO Pins on RPi

$page = $_SERVER['PHP_SELF'];
$sec = "360";

$testArray=
      $trelloUrl=file_get_contents("https://api.trello.com/1/lists/[list_number]/cards?fields=name&key=[API_KEY]&token=[Token]");
      $urlContents = file_get_contents("https://www.rescuetime.com/anapi/data?key=[API_KEY]&perspective=rank&interval=hour&restrict_begin=".date('Y-m-d')."&restrict_end=".date('Y-m-d')."&format=json");
      $urlContentsY = file_get_contents("https://www.rescuetime.com/anapi/data?key=[API_KEY]&perspective=rank&interval=hour&restrict_begin=".date('d.m.Y',strtotime("-1 days"))."&restrict_end=".date('d.m.Y',strtotime("-1 days"))."&format=json");
      $urlContents2 = file_get_contents("https://www.rescuetime.com/anapi/daily_summary_feed?key=[API_KEY]");
      $data = json_decode($urlContents,true);
      $dataY = json_decode($urlContentsY,true);
      $data2 = json_decode($urlContents2,true);
      $trelloData = json_decode($trelloUrl,true);

#echo "Date= ".date('Y-m-d');

#echo $data['rows'][0][3];
#print_r($data);
#print_r($data2);
#print_r($dataY);
#print_r($trelloData);
$posTotal = 0;
$negTotal = 0;
$absTotal = 0;
foreach ($data['rows'] as $key => $value) {
  $productivity = $value[1] * $value[5];
  #print_r($productivity);



  if ($productivity < 0)
{
   $negTotal = $negTotal + $productivity;
}
  $absTotal = $absTotal + abs($productivity);
}

#gets the categaories for each value
$categoriesArray = array();
$seconds = array();
$totalSeconds = 0;
foreach ($data['rows'] as $key => $value) {
  $categoriesArray[] = $value[4];
  $seconds[] = floor($value[1]/60);
  $totalSeconds = $totalSeconds + $value[1];

}


#LED control

$oran=floor(100-(abs($negTotal)*100)/$absTotal);
if ($oran<50) {
    $led->setHex("#FF0000");
}else {
    $led->setHex("#00FF00");
}
#radar graph
$js_array = json_encode($categoriesArray);
#echo "var javascript_array = ". $js_array . ";\n";

$js_array2 = json_encode($seconds);
#echo "var javascript_array = ". $js_array2 . ";\n";
#echo "Negative Total= ".$negTotal;
#echo "<br>";
#echo "Absolute Total= ".$absTotal;

      #for radars yesterday data

$categoriesArrayY = array();
$secondsY = array();
$totalSecondsY = 0;
foreach ($dataY['rows'] as $key => $value) {
  $categoriesArrayY[] = $value[4];
  $secondsY[] = floor($value[1]/60);
  $totalSecondsY = $totalSecondsY + $value[1];
   #echo "it is: ".$categoriesArrayY;
 #echo "<br>";
}

#radar graph
$js_arrayY = json_encode($categoriesArrayY);
#echo "var javascript_array = ". $js_array . ";\n";
$js_array2Y = json_encode($secondsY);


#calculate productive hours
$productiveHours=array();
$distractiveHours=array();
$i=0;
foreach ($data2 as $value) {

if ($i<7) {

$productiveHours[]=$value['all_productive_hours'];
$distractiveHours[]=$value['all_distracting_hours'];
}

$i=$i+1;

}
#print_r($productiveHours);
#print_r($distractiveHours);
$js_productiveHours = json_encode($productiveHours);
$js_distractiveHours = json_encode($distractiveHours);




?>
<!DOCTYPE html>
<html lang="en">
  <head>
     <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

      <title>Productivity</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/css/bootstrap.min.css" integrity="sha384-y3tfxAZXuh4HwSYylfB+J125MxIs6mR5FOHamPBG064zB+AFeWH94NdvaCBm8qnd" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.js"></script>

  <style type="text/css">

  #percent {
    position: relative;
  }
  #percent #myDoughnutChart {
    position: absolute;
  }

  #overlay{
align-items: center;
    color: white;
    position: relative;
    top:168px;
    left: 168px;
    font-size: 150px;
    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
  }
  #doughnut{
    position: relative;
  }
  #textContainer{
    color: white;
    position: fixed;
bottom: 0;
right: 5%;
  }

  #todo{
    color: white;
    margin-left: 8%;
    width: 320px;
  }
  #todo2{
    color: white;
    margin-left: 32%;
    width: 320px;
    margin-top: -150px;
  }
  #container{
background-color: black;
  }
canvas{

}

html{
font-size: 25px;
background-color: black;
}

.container{

font-size: 25px;

}
#doughnut{
  margin-left: 40px;
}
#LineChartContainer{
  width: 35%;
  position: fixed;
  right: 150px;
  bottom:40px;
}
  </style>


  </head>
  <body>
<!--    <div id="container" style="width:50%">

<canvas id="myRadarChart" width="400px" height="400px"></canvas>
    <script>
    Chart.defaults.global.defaultFontSize = 12;
    var ctx = document.getElementById("myRadarChart");
    var myRadarChart = new Chart(ctx, {
      type: 'radar',
      data: {
            labels: ['Running', 'Swimming', 'Eating', 'Cycling'],
            datasets: [{
                data: [10, 10, 10, 10]
    }]
  },
    options: { scale: { pointLabels: {
            fontSize: 20
          } } }
            });
    </script>
    </div>
-->

<div id="container" style="width:100%">
<div id="doughnut">
<div style="margin-left:5%;width:40%; float:left;">
  <div id="percent">

<canvas id="myDoughnutChart" ></canvas>
<div id="overlay"><?php echo floor(100-(abs($negTotal)*100)/$absTotal); ?></div>
</div>
</div>
</div>
<div id="container2" style="margin-left:50%; width:40%;">
<canvas id="myRadarChart" style="position:relative;" ></canvas>

</div>
<div id="textContainer">
<?php echo "Total Time (Galaxy S6 + Asus PC): ".floor($totalSeconds/60)." Minutes"; ?>
</div>
<div id="todo">
<h3>To Do:</h3>

<?php
$i=0;
foreach ($trelloData as $value) {

if ($i<4) {
  echo "&#x25a2";
  echo " ".$value['name'];
  echo "<br>";
}

$i=$i+1;

}

 ?>

</div>
<div id="todo2">
  <?php
  $i=0;
  foreach ($trelloData as $value) {

  if ($i>4) {
    echo "&#x25a2";
    echo " ".$value['name'];
    echo "<br>";
  }

  $i=$i+1;

  }

   ?>
</div>
<div id="LineChartContainer">
  <canvas id="myLineChart"></canvas>
</div>




    <script>

    <?php echo "var productiveHours= ". $js_productiveHours. ";\n"; ?>
    <?php echo "var distractiveHours = ". $js_distractiveHours . ";\n";?>
productiveHours.reverse();
distractiveHours.reverse();
    var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var d = new Date();
    var n = d.getDay();

    for (i = 0; i <=(6- d.getDay()); i++) {
    days.splice(0,0,days[6]);
    days.splice(7,1);
}
console.log(days);




    var ctx = document.getElementById("myLineChart");
    var lineChart = new Chart(ctx, {
      type:'line',
      data: {
        labels:days,
        datasets:[
{
  borderColor:"#00ff00",
  pointColor:"#fff",
  label:"Productive",
  data:productiveHours
},
{
  borderColor:"#ff0000",
  label:"Distractive",
  data:distractiveHours
}
        ]
      },
      options:{
        scales: {
      xAxes: [{
        display: true,
        gridLines: {
          color: "#FFFFFF"
        },
        scaleLabel: {
          display: false,
          labelString: 'Days'
        }
      }],
      yAxes: [{
        display: true,
        gridLines: {
          color: "#FFFFFF"
        },
        scaleLabel: {
          display: true,
          labelString: 'Hours'
        }
      }]
    }
      }
    });


    <?php echo "var categories= ". $js_array . ";\n"; ?>
    <?php echo "var secondsArray = ". $js_array2 . ";\n";?>
    var categoriesRadar = [];
    var secondsRadar = [0,0,0,0,0];

    for (var i=0; i<categories.length;i++){
      if (categories[i]=="Video"||categories[i]=="General Social Networking"||categories[i]=="General Entertainment"||categories[i]=="Games") {
        secondsRadar[0]=secondsRadar[0]+(secondsArray[i]);
      }
      if (categories[i]=="Editing & IDEs"||categories[i]=="General Software Development"||categories[i]=="Video Editing"||categories[i]=="Intelligence") {
        secondsRadar[1]=secondsRadar[1]+(secondsArray[i]);
      }
      if (categories[i]=="General News & Opinion") {
        secondsRadar[2]=secondsRadar[2]+(secondsArray[i]);
      }
      if (categories[i]=="General Reference & Learning"||categories[i]=="General Business"||categories[i]=="Presentation"||categories[i]=="Project Management"||categories[i]=="Design & Planning"||categories[i]=="Writing"||categories[i]=="Engineering & Drafting"||categories[i]=="Search"||categories[i]=="Engineering & Technology") {
        secondsRadar[3]=secondsRadar[3]+(secondsArray[i]);
      }
      if (categories[i]=="Email"||categories[i]=="Instant Message") {
        secondsRadar[4]=secondsRadar[4]+(secondsArray[i]);
      }
    }
    //console.log(secondsRadar);


//YESTERdays data

    <?php echo "var categoriesY= ". $js_arrayY . ";\n"; ?>
    <?php echo "var secondsArrayY = ". $js_array2Y . ";\n";?>
    var categoriesRadarY = [];
    var secondsRadarY = [0,0,0,0,0];

    for (var i=0; i<categoriesY.length;i++){
      if (categoriesY[i]=="Video"||categoriesY[i]=="General Social Networking"||categoriesY[i]=="General Entertainment"||categoriesY[i]=="Games") {
        secondsRadarY[0]=secondsRadarY[0]+(secondsArrayY[i]);
      }
      if (categoriesY[i]=="Editing & IDEs"||categoriesY[i]=="General Software Development"||categoriesY[i]=="Video Editing"||categoriesY[i]=="Intelligence") {
        secondsRadarY[1]=secondsRadarY[1]+(secondsArrayY[i]);
      }
      if (categoriesY[i]=="General News & Opinion") {
        secondsRadarY[2]=secondsRadarY[2]+(secondsArrayY[i]);
      }
      if (categoriesY[i]=="General Reference & Learning"||categoriesY[i]=="General Business"||categoriesY[i]=="Design & Planning"||categoriesY[i]=="Writing"||categoriesY[i]=="Engineering & Drafting"||categoriesY[i]=="Search") {
        secondsRadarY[3]=secondsRadarY[3]+(secondsArrayY[i]);
      }
      if (categoriesY[i]=="Email"||categoriesY[i]=="Instant Message") {
        secondsRadarY[4]=secondsRadarY[4]+(secondsArrayY[i]);
      }
    }
    //console.log(secondsRadar);



Chart.defaults.global.defaultFontColor = '#fff';
    var ctx = document.getElementById("myDoughnutChart");
    var negative = "<?php echo (abs($negTotal)*100)/$absTotal; ?>";
var myDoughnutChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
          labels: ['Distractive', 'Productive'],
          datasets: [{
            backgroundColor: [
        "#ff0000",
        "#00ff00"


      ],
              data: [negative,100-negative]

  }]
},
options:{
elements: { arc: { borderWidth: 0 } },
animation: {
        duration: 0
    }
  }
});
 Chart.defaults.global.defaultFontColor = '#fff';
 Chart.defaults.global.defaultBackgroundColor = '#fff';
 var chartColors = {
 	red: 'rgb(255, 99, 132)',
 	orange: 'rgb(255, 159, 64)',
 	yellow: 'rgb(255, 205, 86)',
 	green: 'rgb(75, 192, 192)',
 	blue: 'rgb(54, 162, 235)',
 	purple: 'rgb(153, 102, 255)',
 	grey: 'rgb(231,233,237)'
 };
var ctx = document.getElementById("myRadarChart");
var color = Chart.helpers.color;
var myRadarChart = new Chart(ctx, {
    type: 'radar',
    data:  {
    labels: ['Entertainment', 'Software Dev.', 'News&Opinion', 'Learning','communication'],
    datasets: [{
        label: "Minutes Spent Today",
        backgroundColor: color(chartColors.red).alpha(0.5).rgbString(),
        pointColor: "rgb(255,255,255)",
        borderColor:"red",
        pointBorderColor:"white",
        pointBackgroundColor:"white",
        data: secondsRadar
    },
    {
      label: "Minutes Spent Yesterday",
      backgroundColor: color(chartColors.blue).alpha(0.5).rgbString(),
      pointColor: "rgb(255,255,255)",
      borderColor:"blue",
      pointBorderColor:"white",
      pointBackgroundColor:"white",
      data: secondsRadarY

    }



  ]
},
options:{
  scale:{
    pointLabels:{
      fontSize:8
    },
    lineArc: true,
    position: "chartArea",

        angleLines: {
            display: true,
            color: "rgb(255,255,255)",
            lineWidth: 1
        },
        gridLines: {
          color: 'rgba(255, 255, 255, 0.4)',
          tickMarkLength: 20
        },

    // label settings
    ticks: {
        //Boolean - Show a backdrop to the scale label
        showLabelBackdrop: false,

        //String - The colour of the label backdrop
        backdropColor: "rgb(255,255,255)",

        //Number - The backdrop padding above & below the label in pixels
        backdropPaddingY: 2,

        //Number - The backdrop padding to the side of the label in pixels
        backdropPaddingX: 2,

        //Number - Limit the maximum number of ticks and gridlines
        maxTicksLimit: 11,
    },
  },
animation: {
        duration: 0
    }
  }

});




    </script>

</div>

  </body>
</html>
