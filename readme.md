# Live Server Monitoring

A websocket solution for live server performance monitoring.

### Usage:
There are two parts:
- server (see: server.php), minimum PHP 7.0 version required
setup the config file and run in the background
- client (see: index.html)
Set the server ip address, port number and click on the start button. Using the HTML5 websocket feature
the Google chart shows the current time based server data Live.

### Benefits:
The server provides periodically the system informations and send them to the connected clients.
It is more efficient than ajax polling requests because the connected client number doesn't add extra work for the system.
If there aren't any connected clients it doesn't generate data on the server.

##### Future todos:
- setup a token and accept only connections with that
- putting the whole solution behind a password protected site
- adding more performance data
- selecting more charts on the client side
- store data in nosql database
- alarm functions
- etc...

### 🌐 Join Me on CoderLegion

[![CoderLegion](https://coderlegion.com/cl_badge_logo1.png)](https://coderlegion.com/user/Istv%C3%A1n+D%C3%B6brentei) Check out my articles and community posts on [CoderLegion!](https://coderlegion.com/user/Istv%C3%A1n+D%C3%B6brentei)
