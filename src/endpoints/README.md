## Usage


This folder contains a couple sub folders which each contain a reference implementation for implementing an API endpoint in an environment.

#### Lumen/Laravel

To use in a Lumen or Laravel application, copy the contents of the appropriate directory into the root directory of your Lumen app.

Then create a route which will serve as the endpoint URL, and attach it to the Steenbag\Tubes\Controllers\WebServiceController@handleRpc method.

