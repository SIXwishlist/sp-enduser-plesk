Plesk extension for the end-user web application for Halon's email gateway. Please read more on http://wiki.halon.se/End-user and http://halon.io

Requirements
-------------
* Plesk version 12.5.30 or later
* Existing end-user web application with `session-transfer.php` enabled

Installation
-------------
1. Clone the project on the Plesk server using `git`
2. Zip the contents of the `/src` directory with `cd sp-enduser-plesk/src && zip -r ../sp-enduser-plesk.zip . && cd ..`
2. Install the plugin with `extension --install sp-enduser-plesk.zip`

You can uninstall the plugin with `extension --uninstall halon-enduser`
