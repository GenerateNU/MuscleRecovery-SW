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

let offloadedData = new Uint8Array(offloadedDataSize);
function handleOffloadData(event) {
  let characteristicValue = event.target.value;
  for (let i = 0; i < offloadedDataSize; i++) {
    offloadedData[i] = characteristicValue.getUint8(i);
    log("Offloaded Data:" + offloadedData[i]);
  }
}

let offloadedDateTime = new Uint8Array(offloadedDateTimeSize);
function handleOffloadDateTime(event) {
  let characteristicValue = event.target.value;
  for (let i = 0; i < offloadedDateTimeSize; i++) {
    offloadedDateTime[i] = characteristicValue.getUint8(i);
    log("Offloaded Datetime:" + offloadedDateTime[i]);
  }
}

let offloadedSessionCount = new Uint8Array(offloadedSessionCountSize);
function handleOffloadSessionCount(event) {
  let characteristicValue = event.target.value;
  for (let i = 0; i < offloadedSessionCountSize; i++) {
    offloadedSessionCount[i] = characteristicValue.getUint8(i);
  }
  let numSessions = offloadedSessionCount[0] + (offloadedSessionCount[1] * 100) + 
    (offloadedSessionCount[2] * 10000) + (offloadedSessionCount[3] * 1000000);
  log("Offloaded Session Count: " + numSessions);
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
