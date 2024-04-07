let index = 0;
let sessionLengthInSeconds = 10;
let mydata = [];
let sessionRecorded = true;
let connected = false;
let device, server, service, characteristics;
let offloadDataChar;
let offloadDateTimeChar;
let streamDataChar;
let sessionStartChar;
let offloadSessionCountChar;
let offloadedDataSize = 10;
let offloadedDateTimeSize = 6;
let offloadedSessionCountSize = 4;
let numSessions;
let offloadedData;
let offloadedDateTime;

function uint8ArrayToStringArray(uint8Array) {
  // Define an array to store the resulting strings
  let stringArray = [];

  // Iterate over each array in the 2D array
  for (let i = 0; i < uint8Array.length; i++) {
      let currentArray = uint8Array[i];
      let string = '';

      // Iterate over each element in the current array
      for (let j = 0; j < currentArray.length; j++) {
          // Convert Uint8 value to its corresponding character representation
          string += String.fromCharCode(currentArray[j]);
      }
      stringArray.push(string);
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
  }

  return dateTimeArray;
}


function handleStreamingData(event) {
  if (event.target.value.byteLength > 0) {
      mydata[index] = event.target.value.getUint8(0); //loads data for a second
      log("index:" + index + ", Data:" + mydata[index]);
      index++;
      if (index == sessionLengthInSeconds) {
          index = 0;
          log('Data From this 10 seconds Session: ');
          mainChart.data.datasets[0].data = mydata;
          mainChart.update();
          mydata = [];
          sessionRecorded = true;
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
      log("Offloaded Data:" + offloadedData[session][i]);
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
      log("Offloaded Datetime:" + offloadedDateTime[session][i]);
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
  

  // Continuously read the characteristic every second
setInterval(() => {
    sendSessionValue("no");
    if (streamDataChar && mydata.length < sessionLengthInSeconds && !sessionRecorded) {
        streamDataChar.readValue()
            .then(handleStreamingData)
            .catch(error => {
                console.error('Error reading characteristic:', error);
            });
    }
}, 1000);
