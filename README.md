# ip2country_CI
Library for converting IP to country name using Codeigniter

The task is solved both by using free geolocation APIs and by using databases.

The databases (for ipv4 and ipv6) are getting from here: http://lite.ip2location.com/database/ip-country. You can find the schemes of databases by following the link (scroll that page down), they are very simple and clear. The data there is free and not ideal, and the result of it's use is checked in code via geolocation APIs.

When using free ipinfo.io geolocation API, after getting country two-character code
we get appropriate country name via countries_names.json file (file has been got here: http://country.io/names.json). Of course, instead of loading JSON-file each time the function is called, an array can be used. JSON-file is used just to show some additional functionality.

As reserve and for result check purposes two additional free APIs ar used:
geoip.nekudo.com and freegeoip.net.

The initial IP address is got by using geolocation APIs too.

To use database query in library the library line in config/autoload.php file was changed to
$autoload['libraries'] = array('database');

Methods descriptions and some comments were added into the code.
