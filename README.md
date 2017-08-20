# ip2country_CI
Test task of converting IP to country name using Codeigniter

The task is solved both by using free geolocation APIs and by using databases.

The databases (for ipv4 and ipv6) are getting from here: http://lite.ip2location.com/database/ip-country
The schemes of databases there are very simple and clear. The data there is free and not ideal and the
result of it's use is checked in code via geolocation APIs.

When using free ipinfo.io geolocation API, after getting country two-character code
we get appropriate country name via countries_names.json file (file has been got here: http://country.io/names.json).

As reserve and for result check purposes two additional free APIs ar used:
geoip.nekudo.com and freegeoip.net.

The initial IP address is got by using geolocation APIs too.

To use database query in library the library line in config/autoload.php file was changed to
$autoload['libraries'] = array('database');

Methods descriptions and some comments are added into the code.
