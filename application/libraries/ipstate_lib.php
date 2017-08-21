<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ipstate_lib {

    protected $countryname;
    

    /**
     * In getCountryName() method we are trying to get country name with ip,
     * For reliability purposes it use 3 different methods:
     * one - firstly used, and other two as reserve.
     *
     * @access	public
     * @param	string
     * @return	string
     */

    public function getCountryName($ip_address)
    {

        //get country name via ipinfo.io and countries_names.json
        $country = $this->getIpToCountry($ip_address);

        if ($country) {

            $this->countryname = $country;
            return $this->countryname;

        } else {

            //get country name from database
            $country = $this->getCountryFromDb($ip_address);

            if ($country) {

                $this->countryname = $country;
                return $this->countryname;

            } else {

                //get country name from APIs response directly, without using IP
                $country = $this->getCountryNameDirectly();

                if($country) {

                    $this->countryname = $country;
                    return $this->countryname;

                } else {

                    echo "Error while getting user country name!";
                }
            }
        }

    }
    

    /**
     * In getIpToCountry() method we firstly get country two-character code via ipinfo.io API
     * and then get appropriate country name via countries_names.json.
     * We check the result via checkIpResult() method.
     *
     * @access	public
     * @param	string
     * @return	string
     */

    public function getIpToCountry($ip_address)
    {
        $url_to_exch_code = "https://ipinfo.io/" . $ip_address . "/country";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_to_exch_code);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $code_string = curl_exec($ch);
        curl_close($ch);

        //Leave only letters in query result
        $code_string = preg_replace("/[^A-Z]+/", "", $code_string);

        //Check also an amount of letters - should be only two
        if(!empty($code_string) && strlen($code_string) == 2) {

            if(is_file(APPPATH . 'libraries/ipstate_src/countries_names.json')){

                $json_names = file_get_contents(APPPATH . 'libraries/ipstate_src/countries_names.json');

                $names_array = json_decode($json_names, true);

                if(array_key_exists($code_string, $names_array)){

                    $country_name = $names_array[$code_string];
                    $country_name = $this->checkIpResult($country_name);
                    return $country_name;

                }else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    /**
     * In getCountryNameDirectly() method we are trying to get
     * country name directly from APIs, without using ip,
     * For reliability purposes it use 2 different methods:
     * one - firstly used, and another one - as reserve.
     *
     * @access	public
     * @return	string
     */

    public function getCountryNameDirectly()
    {

        $url_to_exch_code = "https://freegeoip.net/json/";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_to_exch_code);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);

        $details = json_decode($json, true);

        if(!empty($details['country_name'])) {

            return $details['country_name'];

        } else {

            $url_to_exch_code = "https://geoip.nekudo.com/api/json";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url_to_exch_code);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);

            $details = json_decode($json, true);

            if(!empty($details['country']['name'])) {

                return $details['country']['name'];

            } else {

                return false;

            }
        }

    }
    

    /**
     * In getCountryFromDb() method we search the country name
     * in ip2location_db1 database table if IP has ipv4 version
     * or in ip2location_db1_ipv6 database table if IP has ipv6 version instead.
     * Databases are getting from http://lite.ip2location.com/database/ip-country.
     * They are not perfect and are used just for example.
     * The database schema could be also find there.
     * We check the result via checkIpResult() method.
     *
     * @access	public
     * @param	string
     * @return	string
     */

    public function getCountryFromDb($ip_address)
    {
        //Check the IP version
        if (filter_var($ip_address,FILTER_VALIDATE_IP)) {

            $dbname = "ip2location_db1";

            //Converts a string containing an (IPv4) Internet Protocol dotted address into a long integer
            $ip_address = ip2long($ip_address);

        } elseif(filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {

            $dbname = "ip2location_db1_ipv6";

            //Convert IP address to its corresponding 32–bit decimal number
            $ip_address = $this->ipAddressToIpNumber($ip_address);

        } else {
            return false;
        }
        
        $CI =&get_instance();

        $CI->db->where('ip_from <=', $ip_address);
        $CI->db->where('ip_to >=', $ip_address);
        $res = $CI->db->get($dbname);

        if ($res && $res->num_rows() > 0) {
            $row = $res->row();
            if ($row->country_name && $row->country_name !== '-') {
                $country_name = $row->country_name;
                $country_name = $this->checkIpResult($country_name);
                return $country_name;
            }
        } else {
            return false;
        }
    }


    /**
     * In checkIpResult() method we check the results of getting
     * country name via ipinfo.io API or database and
     * add alternative country name if the check failed.
     *
     * @access	public
     * @param	string
     * @return	string
     */

    public function checkIpResult($country_name)
    {
        $control_country_name = $this->getCountryNameDirectly();

        if($country_name === $control_country_name) {
            return $country_name;
        } else {
            return $country_name . ' or ' . $control_country_name;
        }

    }
    
    
    /**
     * We use ipAddressToIpNumber() method to convert ipv6 address string to number.
     *
     * @access	public
     * @param	string
     * @return	integer
     */

    public function ipAddressToIpNumber($ip_address) {
        $pton = @inet_pton($ip_address);
        if (!$pton) { return false; }
        $number = '';
        foreach (unpack('C*', $pton) as $byte) {
            $number .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }
        return base_convert(ltrim($number, '0'), 2, 10);
    }
    
}
