yourls-cas-auth
===============
A rudimentary [YOURLS](https://github.com/YOURLS/YOURLS) plugin to enable [Central Authentication Service](http://www.jasig.org/cas) for user authentication.

Installation
------------
1. Extract the [latest release of phpCAS](http://downloads.jasig.org/cas-clients/php/current/) to a location php can access.
1. In `user/plugins` create a new folder named `cas-auth`.
1. Extract the latest release of [yourls-cas-auth](https://github.com/jamac/yourls-cas-auth/archive/master.zip) to that folder.
1. Provide connection details and a user white list to `user/config.php`. (details below)
1. Go to your Plugins administration page and activate the _CAS Authentication_ plugin

Usage
-----
When enabled and configured properly this plugin will essentially hijack YOURLS default handling of login/logout, redirecting such requests to your CAS server.

Configuration
-------------

### Required

At minimum you must define the following:

-   `CASAUTH_CAS_PATH` The path to where you extracted phpCAS.

	_This should be an absolute path, though there is a weak attempt at accepting a relative path._

-   `CASAUTH_CAS_HOST` The hostname of your CAS server.

-   `CASAUTH_CAS_URI` The URI your CAS server is responding on.

    _eg_ `/cas`

### Optional

Additionally you may also define the following:

-   `CASAUTH_CAS_VERSION` The version of your CAS server.

	_Defaults to_ `CAS_VERSION_2_0` _as defined by phpCAS_

-   `CASAUTH_CAS_PORT` The port your CAS server is running on.

	_Defaults to_ `443`

-   `CASAUTH_CAS_CACERT` The PEM certificate file name of the CA that emited the cert of the server.

    _You should set this, otherwise the plugin will attempt to connect to CAS with out server validation._

-   `$casauth_user_whitelist` An array of authenticated usernames permitted to administer the site.

    _If omitted or empty all authenticated usernames are permitted to administer the site._

        `$casauth_user_whitelist = array(
            'user-one'
            'user-two'
            ...
        );`

Troubleshooting
---------------
1.  Double check your configuration in `user/config.php`.
1.  Run around in a circle while crying "The sky is falling!"
1.  Check the [issue queue](https://github.com/jamac/yourls-cas-auth/issues).

License
-------

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
