<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Muscle Recovery Data">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src = "back.js"></script>

    <h2>Welcome, <span id="username"></span>!</h2>

  <script>
    // Retrieve username from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    username = urlParams.get('username');

    // Display the username on the page
    document.getElementById('username').textContent = username;
    userDoc = document;
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
// Retrieve username from URL parameters
$user = $_GET['username'];

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "usbw";
$database = "test";

// Establishing connection to the database
$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
} else {
    $sql = "SELECT dateTime, muscleData FROM data WHERE userName = '$user'";
    $result = $mysqli->query($sql);

    $muscleDataArray = [];
    $dateTimeArray = [];

    if ($result->num_rows > 0) {
        // Fetch data and parse muscleData string into an array
        while ($row = $result->fetch_assoc()) {
            $muscleData = explode(',', $row["muscleData"]);
            if ($muscleData == [0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) {

            }
            else {
              $muscleDataArray[] = $muscleData;
              $dateTimeArray[] = $row["dateTime"];
            }
        }
    }

    // Closing the database connection
    $mysqli->close();
}
?>


<h1>Muscle Recovery Data</h1>

<!--<p>This sample illustrates the use of the Web Bluetooth API to retrieve basic-->
<!--  device information from a nearby Bluetooth Low Energy Device. You may want to-->
<!--  check out the <a href="device-info-async-await.html">Device Info (Async-->
<!--    Await)</a> sample.</p>-->

<form>
  <button type="submit">Offload Data</button>
</form>

<form>
<button type="submit" id="sendButton">Start Session</button>
</form>

<div style="margin-top: 20px;"></div>

<!-- Add this button wherever you want on the user.php page -->
<button type="submit" onclick="redirectToLoginPage()">Go back to Login Page</button>

<script>
  // JavaScript function to redirect to the login page
  function redirectToLoginPage() {
    // Replace 'login.php' with the actual URL of your login page
    window.location.href = 'index.html';
  }
</script>


<datalist id="services">
  <option value="Timestamp">timestamp</option>
  <option value="Measurement_array">measurementArray</option>
</datalist>

  <pre id="log"></pre>


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

<div style="display:flex; justify-content: space-between; width:90%; margin:auto;">
  <!-- Main Graph -->
  <div style="width:70%;">
      <canvas id="myChart"></canvas>
      <canvas id="muscleDataChart" width="400" height="400"></canvas>

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
            backgroundColor: 'White',
            borderColor: 'White',
            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        }]
    };
    // Configuration for the main graph
    var mainConfig = {
    type: 'line',
    data: mainData,
    options: {
        scales: {
            x: {
                title: {
                    color: 'white', // Set x-axis title color to white
                    display: true,
                    text: 'Seconds'
                },
                ticks: {
                    color: 'white' // Set x-axis ticks color to white
                }
            },
            y: {
                title: {
                    color: 'white', // Set y-axis title color to white
                    display: true,
                    text: 'EMG Signal Value'
                },
                ticks: {
                    color: 'white' // Set y-axis ticks color to white
                }
            }
        },
        plugins: {
            title: {
                color: 'White',
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

    var muscleDataChart;


    // Create the chart
window.onload = function() {
  var ctx = document.getElementById('myChart').getContext('2d');
  mainChart = new Chart(ctx, mainConfig);

    // Parse muscleData and dateTime arrays from PHP into JavaScript
  var muscleDataArray = <?php echo json_encode($muscleDataArray); ?>;
  var dateTimeArray = <?php echo json_encode($dateTimeArray); ?>;

  // Extract the labels and data for the chart
  var muscleDataLabels = dateTimeArray;
  var muscleDataValues = muscleDataArray;

// Manipulated arrays
var manipulatedDatetimeArray = [];
var manipulatedMuscleDataArray = [];

// Iterate over each set of muscle data
for (var j = 0; j < muscleDataLabels.length; j++) {
    var initialDatetime = muscleDataLabels[j];
    var originalMuscleData = muscleDataArray[j];

    // Iterate over the muscle data values
    for (var i = 0; i < originalMuscleData.length; i++) {
        var parts = initialDatetime.split(' ');
        var datePart = parts[0];
        var timePart = parts[1];
        var timeParts = timePart.split(':');
        var hour = parseInt(timeParts[0]);
        var minute = parseInt(timeParts[1]);
        var second = parseInt(timeParts[2].split('.')[0]);

        second += i;

        if (second >= 60) {
            second -= 60;
            minute++;
        }
        if (minute >= 60) {
            minute -= 60;
            hour++;
        }

        var newDatetime = datePart + ' ' +
            hour.toString().padStart(2, '0') + ':' +
            minute.toString().padStart(2, '0') + ':' +
            second.toString().padStart(2, '0') + '.' +
            '000000';

        manipulatedDatetimeArray.push(newDatetime);
    }
}

var flattenedArray = muscleDataValues.flat().map(function(value) {
    return parseInt(value);
});
manipulatedMuscleDataArray = flattenedArray;


// Zip the two arrays together
let zippedData = manipulatedMuscleDataArray.map((value, index) => [value, manipulatedDatetimeArray[index]]);

// Sort based on the datetime in ascending order
zippedData.sort((a, b) => new Date(a[1]) - new Date(b[1]));

// Unzip the sorted data
let sortedMuscleDataArray = zippedData.map(([value, _]) => value);
let sortedDateTime = zippedData.map(([_, datetime]) => datetime);

  // Create a chart context
  var ctx2 = document.getElementById('muscleDataChart').getContext('2d');

  // log(prevData.labels);
  // log(prevData.datasets[0].data);

  muscleDataChart = new Chart(ctx2, {
        type: 'line',
        data: {
        labels: sortedDateTime,
        datasets: [{
            label: 'Session',
            color: 'white',
            backgroundColor: 'white',
            borderColor: 'white',
            data: sortedMuscleDataArray
        }]
    },
        options: {
            scales: {
              x: {
                    title: {
                      color: 'white',
                      display: true,
                      text: 'Datetimes'
                  },
                ticks: {
                    color: 'white' // Set y-axis ticks color to white
                }
                },
                y: {
                    title: {
                        color: 'white',
                        display: true,
                        text: 'EMG Signal Value'
                    },
                ticks: {
                    color: 'white' // Set y-axis ticks color to white
                }
                }
            },
            plugins: {
                title: {
                  color: 'white',
                    display: true,
                    text: 'Previous Session Data',
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
    });

};


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
    offloadData().then(() => {
        // Accumulate all offloaded data and date-time values into arrays
      const offloadedDataArray = [];
      const offloadedDateTimeArray = [];

      for (let session = 0; session < numSessions; session++) {
        offloadedDataArray.push(JSON.stringify(offloadedData[session]));
        offloadedDateTimeArray.push(JSON.stringify(offloadedDateTime[session]));
      }

      // Create a single form element
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'offloadDataFromUser.php';

    // Create hidden input fields to store arrays of offloadedData and offloadedDateTime
    const offloadedDataInput = document.createElement('input');
    offloadedDataInput.type = 'hidden';
    offloadedDataInput.name = 'offloadedDataArray';
    offloadedDataInput.value = JSON.stringify(offloadedDataArray);
    form.appendChild(offloadedDataInput);
    log(offloadedDataArray);

    const offloadedDateTimeInput = document.createElement('input');
    offloadedDateTimeInput.type = 'hidden';
    offloadedDateTimeInput.name = 'offloadedDateTimeArray';
    offloadedDateTimeInput.value = JSON.stringify(offloadedDateTimeArray);
    form.appendChild(offloadedDateTimeInput);
    log(offloadedDateTimeArray);

    // Create hidden input field for current user
    const currentUserInput = document.createElement('input');
    currentUserInput.type = 'hidden';
    currentUserInput.name = 'currentUser';
    currentUserInput.value = username;
    form.appendChild(currentUserInput);

    // Append the form to the document body and submit it
    document.body.appendChild(form);
    form.submit();
      }).catch(error => {
        console.error('Error sending data to server:', error);
        // Handle error if data sending fails
    });
  }
});


document.getElementById('sendButton').addEventListener('click', function(event) {
  sessionRecorded = false;
  event.stopPropagation();
  event.preventDefault();
  streamData().then(() => {
  event.preventDefault();
  console.log("Button pressed");
  sendSessionValue("yes");
  });
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