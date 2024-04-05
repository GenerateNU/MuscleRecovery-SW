<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Muscle Recovery Data">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <script src = "back.js"></script>

    <h2>Welcome, <span id="username"></span>!</h2>

  <script>
    // Retrieve username from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const username = urlParams.get('username');

    // Display the username on the page
    document.getElementById('username').textContent = username;
  </script>

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


<h1>Unidentified Data</h1>

<!--<p>This sample illustrates the use of the Web Bluetooth API to retrieve basic-->
<!--  device information from a nearby Bluetooth Low Energy Device. You may want to-->
<!--  check out the <a href="device-info-async-await.html">Device Info (Async-->
<!--    Await)</a> sample.</p>-->

<form>
  <button>Connect Wearable</button>
</form>



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

<div id="output" class="output">
  <div id="content"></div>
  <div id="status"></div>
  <pre id="log"></pre>
</div>


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
async function offloadData() {
    let filters = [
        { name: "Generate ECE Muscle Recovery" },
        { services: ["4fafc201-1fb5-459e-8fcc-c5c9c331914b"] }
    ];

    let options = { filters: filters };

    log('Requesting Bluetooth Device...');
    log('with ' + JSON.stringify(options));

    try {
        device = await navigator.bluetooth.requestDevice(options);
        log('Connecting to GATT Server...');
        server = await device.gatt.connect();
        log('Getting Service...');
        service = await server.getPrimaryService("4fafc201-1fb5-459e-8fcc-c5c9c331914b");
        log('Getting Characteristics...');
        characteristics = await Promise.all([
            service.getCharacteristic("f392f003-1c58-4017-9e01-bf89c7eb53bd"), // Offload Data
            service.getCharacteristic("a5b17d6a-68e5-4f33-abe0-e393e4cd7305"), // Datetime
            service.getCharacteristic("beb5483e-36e1-4688-b7f5-ea07361b26a8"), // Data
            service.getCharacteristic("87ffeadd-3d01-45cd-89bd-ec5a6880c009"),
            service.getCharacteristic("630f3455-b378-4b93-8cf5-79225891f94c") // Offload session count
        ]);
        offloadDataChar = characteristics[0];
        offloadDateTimeChar = characteristics[1];
        streamDataChar = characteristics[2];
        sessionStartChar = characteristics[3];
        offloadSessionCountChar = characteristics[4];
        log('Characteristics found. Adding event listeners...');

        //TODO: NO IDEA WHY THE EVENT LISTENERS ARE NOT ADDED
        // Add event listeners for characteristics
        await Promise.all([
            // session count must be offloaded first to construct nested array of offloaded data with number of offloaded sessions 
            offloadSessionCountChar.addEventListener('characteristicvaluechanged', handleOffloadSessionCount),
            offloadDataChar.addEventListener('characteristicvaluechanged', handleOffloadData),
            offloadDateTimeChar.addEventListener('characteristicvaluechanged', handleOffloadDateTime)
        ]);

        log("Event listeners added");

        // Read the characteristic values after all event listeners have been added
        await offloadSessionCountChar.readValue();
        await offloadDataChar.readValue();
        await offloadDateTimeChar.readValue();
        connected = true;
    } catch (error) {
        console.error('Bluetooth Error:', error);
    }
}


    
  document.querySelector('form').addEventListener('submit', function (event) {
    event.stopPropagation();
    event.preventDefault();

    if (isWebBluetoothEnabled()) {
      ChromeSamples.clearLog();
      offloadData();
    }
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
