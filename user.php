<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Muscle Recovery Data">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src = "back.js"></script>

    <h2>Welcome, <span id="username"></span>!</h2>

  <script>
    // Retrieve username from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const username = urlParams.get('username');

    // Display the username on the page
    document.getElementById('username').textContent = username;
  </script>

  <title>Muscle Recovery Data</title>
  <script>
    // Add a global error event listener early on in the page load, to help ensure that browsers
    // which don't support specific functionality still end up displaying a meaningful message.
    window.addEventListener('error', function (error) {
      if (ChromeSamples && ChromeSamples.setStatus) {
        console.error(error);
        ChromeSamples.setStatus(error.message + ' (Your browser may not support this feature.)');
        error.preventDefault();
      }
    });
  </script>

  <link rel="stylesheet" href="../styles/main.css">

</head>

<body>

<?php

$host = "localhost";
$username = "root";
$user_pass = "usbw";
$database_in_use = "test";

$mysqli = new mysqli($host, $username, $user_pass, $database_in_use);

if ($mysqli->connect_errno) {
   echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

echo $mysqli->host_info . "\n";


$sql = "SELECT userName, muscleData, dateTime FROM data";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    echo "userName: " . $row["userName"]. " - muscleData: " . $row["muscleData"]. " " . $row["dateTime"]. "<br>";
  }
} else {
  echo "0 results";
}
$mysqli->close();

?>


<h1>Muscle Recovery Data</h1>

<!--<p>This sample illustrates the use of the Web Bluetooth API to retrieve basic-->
<!--  device information from a nearby Bluetooth Low Energy Device. You may want to-->
<!--  check out the <a href="device-info-async-await.html">Device Info (Async-->
<!--    Await)</a> sample.</p>-->

<form>
  <button>Connect Wearable</button>
</form>

<form>
<button id="sendButton">Start Session</button>
</form>

<datalist id="services">
  <option value="Timestamp">timestamp</option>
  <option value="Measurement_array">measurementArray</option>
</datalist>


<script>
  var ChromeSamples = {
    log: function () {
      var line = Array.prototype.slice.call(arguments).map(function (argument) {
        return typeof argument === 'string' ? argument : JSON.stringify(argument);
      }).join(' ');

      document.querySelector('#log').textContent += line + '\n';
    },

    clearLog: function () {
      document.querySelector('#log').textContent = '';
    },

    setStatus: function (status) {
      document.querySelector('#status').textContent = status;
    },

    setContent: function (newContent) {
      var content = document.querySelector('#content');
      while (content.hasChildNodes()) {
        content.removeChild(content.lastChild);
      }
      content.appendChild(newContent);
    }
  };
</script>

<h3>Live Output</h3>
<div id="output" class="output">
  <div id="content"></div>
  <div id="status"></div>
  <pre id="log"></pre>
</div>

<div style="display:flex; justify-content: space-between; width:90%; margin:auto;">
  <!-- Main Graph -->
  <div style="width:70%;">
      <canvas id="myChart"></canvas>
  </div>

  <!-- Side Column with Smaller Graphs -->
  <div style="width:20%;">
      <canvas id="smallGraph1" style="margin-bottom:10px;"></canvas>
      <canvas id="smallGraph2" style="margin-bottom:10px;"></canvas>
      <canvas id="smallGraph3"></canvas>
  </div>
</div>

<script>
    var mainChart;
   // Sample data for the main graph
    const mainLabels = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10'];
    var mainData = {
        labels: mainLabels,
        datasets: [{
            label: 'Session',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: []
        }]
    };

    // Configuration for the main graph
    var mainConfig = {
        type: 'line',
        data: mainData,
        options: {
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'EMG Signal Value'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Session Data',
                    position: 'top',
                    font: {
                        size: 32
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    };


    // Create the chart
window.onload = function() {
  var ctx = document.getElementById('myChart').getContext('2d');
  mainChart = new Chart(ctx, mainConfig);
};


  function onButtonClick() {
    connectDevice();
  }

</script>


<script>
  log = ChromeSamples.log;

  function isWebBluetoothEnabled() {
    if (navigator.bluetooth) {
      return true;
    } else {
      ChromeSamples.setStatus('Web Bluetooth API is not available.\n' +
                              'Please make sure the "Experimental Web Platform features" flag is enabled.');
      return false;
    }
  }
</script>

<script>
  document.querySelector('form').addEventListener('submit', function (event) {
    event.stopPropagation();
    event.preventDefault();

    if (isWebBluetoothEnabled()) {
      ChromeSamples.clearLog();
      onButtonClick();
    }
  });

  // Event listener for button press
  document.getElementById('sendButton').addEventListener('click', async function(event) {
    event.preventDefault();
    console.log("Button pressed");
    sendSessionValue("yes");
    sessionRecorded = false;
    log("pressed");
  });
</script>


<script>
  /* jshint ignore:start */
  (function (i, s, o, g, r, a, m) {
    i['GoogleAnalyticsObject'] = r;
    i[r] = i[r] || function () {
      (i[r].q = i[r].q || []).push(arguments)
    }, i[r].l = 1 * new Date();
    a = s.createElement(o),
      m = s.getElementsByTagName(o)[0];
    a.async = 1;
    a.src = g;
    m.parentNode.insertBefore(a, m)
  })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
  ga('create', 'UA-53563471-1', 'auto');
  ga('send', 'pageview');
  /* jshint ignore:end */
</script>
</body>
</html>
