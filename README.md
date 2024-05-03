## Muscle Recovery Software
The main functions of the website can be split into three different pages, the login page (index.html), user page (user.php), and admin page (admin.php) as shown in appendices X-X. The login page queries MySQL to check if the inputted username in the Login text box exists (userNameCheck.php script). It also checks if the inputted username in the Create User text box already exists and creates a new user if it doesn’t already (createUser.php script). An additional feature is the automatic creation of ADMIN on the first creation of a new user. This allows the user to access the admin page in the very beginning of the program’s lifetime. Upon a valid input into the login text box, the page redirects to the user page.

In the user page, there exists two buttons: Connect Wearable, and Start Session as well as two charts that display streamed data and all previous sessions. On the Connect Wearable button click, an event listener (in back.js) uses the Web Bluetooth API to connect to the GATT server (General Attributes Server) of the Muscle Recovery Wearable as well as its primary service and characteristics (pData, pSessionStart). On the Start Session button click, another event listener writes a value to pSessionStart to indicate the beginning of a new session. Then the Javascript function begins reading a uint8 value from pData every second. This value is plotted on the main chart, continuously updating the visualization of muscle activation in real time. At the end of the session, it is saved to the database. The chart below the main chart displays data from all of the user’s past sessions and offers insight into the muscle recovery progress. This chart is automatically updated every time the site redirects to the user page by querying the database for all sessions with the corresponding username. 

In the admin page, there are four buttons: Connect Wearable, OffloadData, Show Unassigned Sessions, and Update Usernames. The Connect Wearable button serves a different purpose on the admin page. It connects to the GATT server and primary service as before but connects to different characteristics (pDateTime, pOffloadData, pNumSessions).  On button click of Connect Wearable, the event listener offloads an array of uint8 from pNumSessions, pOffloadData as well as from pDateTime, allowing us to retrieve all of the session data that was stored on the wearable’s flash memory and store them in an appropriate 2D array of size pNumSessions. The user must then click the OffloadData button to send all of the sessions in this new 2D array into the database (offloadData.php). Since there is no way to input the username on the physical device, the sessions that are sent over must be assigned a username. This led us to develop the Show Unassigned Session button, which displays rows of unassigned sessions with their datetimes as well as a text box to insert a username (get_data.php). Upon Update Usernames button click, we assign all corresponding database rows with the new usernames (update_data.php).

## Directory Structure:
```
.
|
|───production  # contains files needed for final prototype
|   |
|   |───eCAD    # contains the KiCAD project
|   └───src     # contains the code needed for deployment
|
|───testing     # contains files relevant to testing components
|
└───README.md   # this file!
```

## Preferred Tools
- KiCAD for PCB development
- Visual Studio Code / PlatformIO for code development
- Git CLI / GitHub Desktop for managing Git

## Branches
> To isolate conflicts for schematics and code, we use two branches to actually develop off of depending on discipline
- `develop/ecad` is for schematic development, please create PR's into this branch for schematics and operate in this branch for layout
- `develop/src` is for code development, please create PR's into this branch for features

## References
- Schematics + Components (WIP)

## Practice Good Git Hygiene!
1. Only commit files you intended to change
2. Create branches for each feature, and larger branches for each development effort (i.e. ```develop/*```)
3. Check your branch before starting work
4. Pull frequently to avoid conflicts
5. Make Pull Requests when you are ready to merge into a larger branch

## Git Resources

* [Setting up Git](https://fanatical-colossus-434.notion.site/Git-Installation-and-Setup-d07b7d1ab5544424876f9fd3b4a0b312)
* [Intro to Git Crash Course](https://fanatical-colossus-434.notion.site/Crash-Course-Intro-to-Git-809641611da9478b8f9cca8fd97e49fe)
