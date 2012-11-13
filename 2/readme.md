# Timestreams API (v. 2.0.0-Alpha-0.1) #

	    
The Timestreams API is documented [here](http://timestreams.wp.horizon.ac.uk/wp-content/plugins/timestreams/2).

This API:

1. Implements Timestreams as RESTful in PHP using the Slim framework.
2. Shows the consumption of services using jQuery

Set Up
-------

1. Install and activate Timestreams plugin in Wordpress
2. Ensure the api/index.php getConnection() function variables match the database configuration. 
3. Ensure the main.js rootURL variable matches the deployment configuration.
4. Test the functions using your browser or curl.

To Do:
------
1. Implement functions
2. Write routes
3. Replace db config with wp-config.php values

Contributing
------------

1. Fork it.
2. Create a branch (`git checkout -b my_markup`)
3. Commit your changes (`git commit -am "Added Snarkdown"`)
4. Push to the branch (`git push origin my_markup`)
5. Create an [Issue][1] with a link to your branch
6. Enjoy a refreshing Diet Coke and wait

License
------------
Copyright (C) 2012 Horizon Digital Economy Research Institute

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
