let index = 0;
let sessionLengthInSeconds = 10;
let mydata = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
let sessionRecorded = true;
let connected = false;
let device, server, service, characteristics;
let offloadDataChar;
let offloadDateTimeChar;
let streamDataChar;
let sessionStartChar;
let sendDateTimeChar;
let offloadSessionCountChar;
let offloadedDataSize = 10;
let offloadedDateTimeSize = 6;
let offloadedSessionCountSize = 4;
let numSessions;
let offloadedData;
let offloadedDateTime;
let username;
let userDoc;

async function streamData() {
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
        service.getCharacteristic("beb5483e-36e1-4688-b7f5-ea07361b26a8"),  // Data
        service.getCharacteristic("87ffeadd-3d01-45cd-89bd-ec5a6880c009"),
        service.getCharacteristic("cc7d583a-5c96-4299-8f18-3dde34a6b1d7") // setting datetime on esp
    ]);
    offloadDataChar = characteristics[0];
    offloadDateTimeChar = characteristics[1];
    streamDataChar = characteristics[2];
    sessionStartChar = characteristics[3];
    sendDateTimeChar = characteristics[4];
    log('Characteristics found. Adding event listeners...');
    await streamDataChar.addEventListener('characteristicvaluechanged', handleStreamingData);
    //await streamDataChar.readValue();
    //await offloadDateTimeChar.readValue();//[offloadDataChar, offloadDateTimeChar, streamDataChar]
    connected = true;
  } catch (error) {
    console.error('Bluetooth Error:', error);
    throw error; // Propagate the error to the caller
  }
}

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
          service.getCharacteristic("630f3455-b378-4b93-8cf5-79225891f94c"), // Offload session count
          service.getCharacteristic("cc7d583a-5c96-4299-8f18-3dde34a6b1d7") // setting datetime on esp
      ]);
      offloadDataChar = characteristics[0];
      offloadDateTimeChar = characteristics[1];
      streamDataChar = characteristics[2];
      sessionStartChar = characteristics[3];
      offloadSessionCountChar = characteristics[4];
      sendDateTimeChar = characteristics[5];
      log('Characteristics found. Adding event listeners...');

      // Add event listeners for characteristics
      await Promise.all([
          // session count must be offloaded first to construct nested array of offloaded data with number of offloaded sessions 
          offloadSessionCountChar.addEventListener('characteristicvaluechanged', handleOffloadSessionCount),
          offloadDataChar.addEventListener('characteristicvaluechanged', handleOffloadData),
          offloadDateTimeChar.addEventListener('characteristicvaluechanged', handleOffloadDateTime)
      ]);
      
      // Read the characteristic values after all event listeners have been added
      await offloadSessionCountChar.readValue();
      await offloadDataChar.readValue();
      await offloadDateTimeChar.readValue();

      await sendDatetime();

      //await streamData();
      //streamDataChar.addEventListener('characteristicvaluechanged', handleStreamingData);
      //connected = true;

      log("Event listeners added");
  } catch (error) {
      console.error('Bluetooth Error:', error);
  }
}

async function sendDataToServer(offloadedDataArray, offloadedDateTimeArray) {
  try {
    // Construct the payload to send to the server
    const payload = {
      offloadedDataArray: JSON.stringify(offloadedDataArray),
      offloadedDateTimeArray: JSON.stringify(offloadedDateTimeArray)
    };

    // Send the payload to the server using fetch or any other appropriate method
    const response = await fetch('offloadDataFromUser.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    // Check if the server response is successful
    if (!response.ok) {
      throw new Error('Server responded with error');
    }

    // Data successfully sent to the server
    console.log('Data sent to server successfully');
  } catch (error) {
    throw new Error('Error sending data to server: ' + error.message);
  }
}

function uint8ArrayToStringArray(uint8Array) {
  // Define an array to store the resulting strings
  let stringArray = [];

  // Iterate over each array in the 2D array
  for (let i = 0; i < uint8Array.length; i++) {
      let currentArray = uint8Array[i];
      let string = currentArray.toString();
      stringArray.push(string);
      log(string);
  }

  return stringArray;
}

function uint8ArrayToDateTimeArray(uint8Array) {
  // Define an array to store the resulting datetime strings
  let dateTimeArray = [];

  // Iterate over each array in the 2D array
  for (let i = 0; i < uint8Array.length; i++) {
      let currentArray = uint8Array[i];

      // Extract year, month, day, hour, minute, and second from the current array
      let year = currentArray[0];
      let month = currentArray[1];
      let day = currentArray[2];
      let hour = currentArray[3];
      let minute = currentArray[4];
      let second = currentArray[5];

      // Create a datetime string in the format 'YYYY-MM-DD HH:MM:SS'
      let datetime = `${year}-${month}-${day} ${hour}:${minute}:${second}`;

      // Push the resulting datetime string to the dateTimeArray
      dateTimeArray.push(datetime);
      log(datetime);
  }

  return dateTimeArray;
}


function handleStreamingData(event) {
  if (event.target.value.byteLength > 0) {
    mydata[index] = event.target.value.getUint8(0); // Load data for a second
    log("Index:" + index + ", Data:" + mydata[index]);
    index++;
    mainChart.data.datasets[0].data = mydata;
    mainChart.update();
    if (index == sessionLengthInSeconds) {
      sessionRecorded = true;
      index = 0;
      log('Data From this 10 seconds Session: ');

      event.stopPropagation();
      event.preventDefault();
      ChromeSamples.clearLog();

      const urlParams = new URLSearchParams(window.location.search);
      const username = urlParams.get('username');

      // Get the current datetime
      const currentDatetime = new Date();

      // Format the datetime string
      let formattedDatetime = 
        currentDatetime.getFullYear() + '-' +
        ('0' + (currentDatetime.getMonth() + 1)).slice(-2) + '-' +
        ('0' + currentDatetime.getDate()).slice(-2) + ' ' +
        ('0' + currentDatetime.getHours()).slice(-2) + ':' +
        ('0' + currentDatetime.getMinutes()).slice(-2) + ':' +
        ('0' + currentDatetime.getSeconds()).slice(-2) + '.' +
        ('00000' + currentDatetime.getMilliseconds()).slice(-6);

      // Format the offloaded data as a comma-separated string enclosed in double quotes
      const formattedData = '"' + mydata.join(',') + '"';

      // Create a single form element
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = 'insertStreamedData.php';

      // Create hidden input fields to store formatted offloaded data and date-time
      const offloadedDataInput = document.createElement('input');
      offloadedDataInput.type = 'hidden';
      offloadedDataInput.name = 'offloadedDataArray';
      offloadedDataInput.value = formattedData;
      form.appendChild(offloadedDataInput);

      // Create hidden input field for formatted date-time
      const offloadedDateTimeInput = document.createElement('input');
      offloadedDateTimeInput.type = 'hidden';
      offloadedDateTimeInput.name = 'offloadedDateTimeArray';
      offloadedDateTimeInput.value = JSON.stringify(formattedDatetime);
      form.appendChild(offloadedDateTimeInput);

      // Create hidden input field for current user
      const currentUserInput = document.createElement('input');
      currentUserInput.type = 'hidden';
      currentUserInput.name = 'currentUser';
      currentUserInput.value = username;
      form.appendChild(currentUserInput);

      // Append the form to the document body and submit it
      document.body.appendChild(form);
      form.submit();
      mydata = [];
    }
  }
}



    let offloadedSessionCount = new Uint8Array(offloadedSessionCountSize);
    function handleOffloadSessionCount(event) {
      let characteristicValue = event.target.value;
      for (let i = 0; i < offloadedSessionCountSize; i++) {
        offloadedSessionCount[i] = characteristicValue.getUint8(i);
      }
      numSessions = (offloadedSessionCount[0] + (offloadedSessionCount[1] * 100) + 
        (offloadedSessionCount[2] * 10000) + (offloadedSessionCount[3] * 1000000)) / (offloadedDataSize + offloadedDateTimeSize);
      log("Offloaded Session Count: " + numSessions);
    }


function handleOffloadData(event) {
  offloadedData = [];
  let characteristicValue = event.target.value;
  for (let session = 0; session < numSessions; session++) {
    offloadedData[session] = [];
    for (let i = 0; i < offloadedDataSize; i++) {
      offloadedData[session][i] = characteristicValue.getUint8(i + (session * offloadedDataSize));
    }
  }
  offloadedData = uint8ArrayToStringArray(offloadedData);
}

function handleOffloadDateTime(event) {
  offloadedDateTime = [];
  let characteristicValue = event.target.value;
  for (let session = 0; session < numSessions; session++) {
    offloadedDateTime[session] = [];
    for (let i = 0; i < offloadedDateTimeSize; i++) {
      offloadedDateTime[session][i] = characteristicValue.getUint8(i + (session * offloadedDateTimeSize));
    }
  }
  offloadedDateTime = uint8ArrayToDateTimeArray(offloadedDateTime);
}

async function sendSessionValue(val) {
    try {
      let char = await service.getCharacteristic("87ffeadd-3d01-45cd-89bd-ec5a6880c009");
      const encodedValue = new TextEncoder().encode(val);
      char.writeValue(encodedValue);
      console.log(`Sent value: ${val}`);
      encodedValue = new TextEncoder().encode("no");
      char.writeValue(encodedValue);
    
    } catch (error) {
      console.error('Bluetooth Error:', error);
    }
  }
  
async function sendDatetime() {
    try {
        const now = new Date();
        const year = now.getFullYear() - 2000; // Only keep the last two digits of the year
        const month = now.getMonth() + 1; // Months are zero-based, so add 1
        const day = now.getDate();
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const seconds = now.getSeconds();

        // Create a Uint8Array with the individual components
        const datetimeArray = new Uint8Array([year, month, day, hours, minutes, seconds]);

        let char = await service.getCharacteristic("cc7d583a-5c96-4299-8f18-3dde34a6b1d7"); // setting datetime on esp
        
        // Send the Uint8Array
        await char.writeValue(datetimeArray);

        // Log the formatted datetime
        const formattedDatetime = formatDate(now);
        log(formattedDatetime);
    } catch (error) {
        console.error('Bluetooth Error:', error);
    }
}


  // Continuously read the characteristic every second
setInterval(() => {
    sendSessionValue("no");
    if (!sessionRecorded) {
        streamDataChar.readValue()
            .catch(error => {
                console.error('Error reading characteristic:', error);
            });
    }
}, 1000);