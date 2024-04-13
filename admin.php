<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Muscle Recovery Data">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">

  <script src = "back.js"></script>

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

<h2>Welcome, ADMIN!</h2>


<h1>Unidentified Data</h1>

<!--<p>This sample illustrates the use of the Web Bluetooth API to retrieve basic-->
<!--  device information from a nearby Bluetooth Low Energy Device. You may want to-->
<!--  check out the <a href="device-info-async-await.html">Device Info (Async-->
<!--    Await)</a> sample.</p>-->

<form>
  <button type="submit">Connect Wearable</button>
</form>

<!-- Create User Form -->
<form type="submit" id="offloadDataForm" name="offloadDataForm">
  <button type="submit">Offload Data</button>
</form>


<form id="updateForm" method="POST" action="update_data.php">
    <button type="submit" id="showUnassignedSessions">Show Unassigned Sessions</button>
    <div id="unassignedSessions"></div>

    <div style="margin-top: 20px;"></div>

    <!-- Submit button -->
    <button type="submit">Update Usernames</button>
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

// Add an event listener to the "Connect Wearable" button
document.querySelector('form').addEventListener('submit', function (event) {
    event.stopPropagation();
    event.preventDefault();

    if (isWebBluetoothEnabled()) {
        ChromeSamples.clearLog();
        offloadData();
    }
});

document.getElementById('updateForm').addEventListener('submit', function(event) {
      log("HERE");

      // Create an XMLHttpRequest object
      var xhr = new XMLHttpRequest();

      // Define the AJAX request
      xhr.open('POST', 'update_data.php', true);

      // Set up the callback function for when the request completes
      xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
          // Request was successful
          // Optionally, display a success message or perform any other action
        } else {
          // Request failed
          console.error('Request failed. Status:', xhr.status);
        }
      };

      // Send the form data
      xhr.send(new FormData(this));
    });

 // Add an event listener to the "Offload Data" button
 document.getElementById('offloadDataForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent the default form submission behavior

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
    form.action = 'offloadData.php';

    // Create hidden input fields to store arrays of offloadedData and offloadedDateTime
    const offloadedDataInput = document.createElement('input');
    offloadedDataInput.type = 'hidden';
    offloadedDataInput.name = 'offloadedDataArray';
    offloadedDataInput.value = JSON.stringify(offloadedDataArray);
    form.appendChild(offloadedDataInput);

    const offloadedDateTimeInput = document.createElement('input');
    offloadedDateTimeInput.type = 'hidden';
    offloadedDateTimeInput.name = 'offloadedDateTimeArray';
    offloadedDateTimeInput.value = JSON.stringify(offloadedDateTimeArray);
    form.appendChild(offloadedDateTimeInput);

    // Append the form to the document body and submit it
    document.body.appendChild(form);
    form.submit();
});

document.getElementById('showUnassignedSessions').addEventListener('click', function() {
  event.preventDefault(); // Prevent default form submission
    // Create an XMLHttpRequest object
    var xhr = new XMLHttpRequest();

    // Define the AJAX request
    xhr.open('GET', 'get_data.php', true);

    // Set up the callback function for when the request completes
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            // Request was successful
            // Display the response in a div with id "unassignedSessions"
            document.getElementById('unassignedSessions').innerHTML = xhr.responseText;
        } else {
            // Request failed
            console.error('Request failed. Status:', xhr.status);
        }
    };

    // Send the request
    xhr.send();
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