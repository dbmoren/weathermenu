# weathermenu
A script to retrieve data from a Netatmo weather station and display it in a Mac menu bar.

Intended for use with [SwiftBar](https://swiftbar.app).

# Configuration

Note that this script is written in PHP, which I believe is no longer included in the operating system as of macOS Monterey, so you'll need to install it via [Homebrew](https://brew.sh). Also note that Homebrew installs on different locations whether you're using an Apple or Intel processor; this version of the script expects PHP in `/opt/homebrew/bin/php`, which is the default installation location on Apple silicon.

(If you're running on Intel, this should probably be `/usr/local/bin/php` instead, but you can clarify by running the command `which php` in Terminal and using the resulting path.

You'll need a handful of details to configure the script, including the username and password for your Netatmo account, and your module's MAC address (probably easiest to obtain via your Wi-Fi router). 

In addition, you'll also need to create an application via [Netatmo's developer page](https://dev.netatmo.com/apps/). Once you do, it will provide you with two key pieces of information: a client ID (a string of static characters identifying your app), and a client secret (essentially a password). These two pieces of information are used to retrieve the authorization token, as described above. 

Once you've entered your information in the script, drop it in your SwiftBar plugin folder, and it ought to retrieve the necessary info.

Note that the script is being provided as is for your entertainment; I welcome feedback, but I can't necessarily accommodate troubleshooting requests for particular setups. Best of luck, and enjoy!