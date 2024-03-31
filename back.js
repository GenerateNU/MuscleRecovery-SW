let index = 0;
let sessionLengthInSeconds = 10;
let mydata = [];
let sessionRecorded = true;
let connected = false;
let device, server, service, characteristics
let offloadDataChar
let offloadDateTimeChar
let streamDataChar
let sessionStartChar

async function connectDevice() {
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
          service.getCharacteristic("87ffeadd-3d01-45cd-89bd-ec5a6880c009")
      ]);
      offloadDataChar = characteristics[0];
      offloadDateTimeChar = characteristics[1];
      streamDataChar = characteristics[2];
      sessionStartChar = characteristics[3];
      log('Characteristics found. Adding event listeners...');
      offloadDataChar.addEventListener('characteristicvaluechanged', handleOffloadData);
      offloadDateTimeChar.addEventListener('characteristicvaluechanged', handleOffloadDateTime);
      streamDataChar.addEventListener('characteristicvaluechanged', handleStreamingData);
      await offloadDataChar.readValue();
      await offloadDateTimeChar.readValue();//[offloadDataChar, offloadDateTimeChar, streamDataChar]
      connected = true;
    } catch (error) {
      console.error('Bluetooth Error:', error);
    }
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

let offloadedData = new Uint8Array(10);
function handleOffloadData(event) {
  let characteristicValue = event.target.value;
  for (let i = 0; i < offloadedData.length; i++) {
    offloadedData[i] = characteristicValue.getUint8(i);
    log("Offloaded Data:" + offloadedData[i]);
  }
}

let offloadedDateTime = new Uint8Array(4);
function handleOffloadDateTime(event) {
  let characteristicValue = event.target.value;
  for (let i = 0; i < offloadedDateTime.length; i++) {
    offloadedDateTime[i] = characteristicValue.getUint8(i);
    log("Offloaded Datetime:" + offloadedDateTime[i]);
  }
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
