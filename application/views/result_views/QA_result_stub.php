
<link type="text/css" href="../../assets/css/base.css" rel="stylesheet" />
<link type="text/css" href="../../assets/css/PieChart.css" rel="stylesheet" />
<link type="text/css" href="../../assets/css/BarChart.css" rel="stylesheet" />
<!--[if IE]><script language="javascript" type="text/javascript" src="../../assets/js/excanvas.js"></script><![endif]-->

<script language="javascript" type="text/javascript" src="../../assets/js/jit-min.js"></script>
<script>

var j = jQuery.noConflict();

var labelType, useGradients, nativeTextSupport, animate, nativeCanvasSupport;


var updateTimer;
var pieChart;
var barChart;

j(document).ready(function(){
    //init data
   
		labelType = "Native";
		nativeTextSupport = "Native";
		useGradients = false;
		animation = true;


	  var ua = navigator.userAgent,
	      iStuff = ua.match(/iPhone/i) || ua.match(/iPad/i),
    typeOfCanvas = typeof HTMLCanvasElement,
    nativeCanvasSupport = (typeOfCanvas == 'object' || typeOfCanvas == 'function'),
		textSupport = nativeCanvasSupport 
    && (typeof document.createElement('canvas').getContext('2d').fillText == 'function');
	  //I'm setting this based on the fact that ExCanvas provides text support for IE
	  //and that as of today iPhone/iPad current text support is lame
	  labelType = (!nativeCanvasSupport || (textSupport && !iStuff))? 'Native' : 'HTML';
	  nativeTextSupport = labelType == 'Native';
	  useGradients = false;
	  animation = !(iStuff || !nativeCanvasSupport);



		barChart = new $jit.BarChart({
		      //id of the visualization container
		      injectInto: 'infovisBar',
		      //whether to add animations
		      animate: true,
		      //horizontal or vertical barcharts
		      orientation: 'vertical',
		      //bars separation
		      barsOffset: 20,
		      //visualization offset
		      offset: 10,
		      //labels offset position
		      labelOffset: 5,
		      //bars style
		      type: useGradients? 'stacked:gradient' : 'stacked',
		      //whether to show the aggregation of the values
		      showAggregates:true,
		      //whether to show the labels for the bars
		      showLabels:true,
		      //labels style
		      Label: {
		        type: labelType, //Native or HTML
		        size: 13,
		        family: 'Titillium',
		        color: 'black'
		      },
		      //add tooltips
		      Tips: {
		        enable: true,
		        onShow: function(tip, elem) {
		          tip.innerHTML = "<b>" + elem.name + "</b>: " + elem.value;
		        }
		      }
		    });



    //init PieChart
   pieChart = new $jit.PieChart({
      //id of the visualization container
      injectInto: 'infovisPie',
      //whether to add animations
      animate: true,
      //offsets
      offset: 35,
      sliceOffset: 0,
      labelOffset: 20,
      //slice style
      type: useGradients? 'stacked:gradient' : 'stacked',
      //whether to show the labels for the slices
      showLabels:true,
      //resize labels according to
      //pie slices values set 7px as
      //min label size
      resizeLabels: 7,
      //label styling
      Label: {
        type: labelType, //Native or HTML
        size: 30,
        family: 'Titillium',
        color: 'black'
      },
      //enable tips
      Tips: {
        enable: true,
        onShow: function(tip, elem) {
           tip.innerHTML = "<b>" + elem.name + "</b>: " + elem.value;
        }
      }
    });
    //load JSON data.


	j.getJSON(window.location.href + "/true", function(data) {

		pieChart.loadJSON(data);
		for(var i in data.values) {
			if(data.values[i].values[0] == "0.0001") {
				data.values[i].values[0] = 0;
			}
			
		}
		
		barChart.loadJSON(data);
		if(data.questionIsOpen) {
			updateTimer= setInterval("updatePieChart();", 5000);
		
		}

	});
	
	
  
});


function updatePieChart() {
	
	j.getJSON(window.location.href + "/true", function(data) {
	
		pieChart.updateJSON(data);

		for(var i in data.values) {
			if(data.values[i].values[0] == "0.0001") {
				data.values[i].values[0] = 0;
			}

		}

		barChart.updateJSON(data);
		if(!data.questionIsOpen) {
			clearInterval(updateTimer);
		}
	});
	
	
}


</script>




<div id="center-container">
		
		<?=$this->load->view("result_views/result_legend_view"); ?>
	
   		<div id="chart_container">
	   <div id="infovisPie"></div>    
	   <div id="infovisBar"></div>
		</div>
</div>


