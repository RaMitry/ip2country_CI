<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ipstate extends CI_Controller {

    /**
     * In constructor we load our library file.
     */

    public function __construct()
    {
        parent::__construct();

        $this->load->library('ipstate_lib');
    }

    /**
     * In index we call getInternetIp() method to get user's IP
     * and then call to library getCountryName() method with this IP
     * to get country name.
     * The result is simply printed to browser tab via echo.
     */


    public function index()
    {
        $ip_address = $this->getInternetIp();

        if($ip_address){

            $country_name = $this->ipstate_lib->getCountryName($ip_address);

            if(!empty($country_name)) {

                echo "Your IP is " . $ip_address . ",  your are from " .$country_name;

            } else {

                echo "Error while getting user country name!";

            }


        } else {

            echo "Error while getting user IP!";
        }

    }


    /**
     * getInternetIp() function is back-end method to get user's IP
     * via free geolocation APIs.
     * For reliability purposes it use 3 APIs:
     * one - firstly used, and other two as reserve.
     */

    public function getInternetIp()
    {
        $url_to_exch_code = "https://ipinfo.io/json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_to_exch_code);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);

        $details = json_decode($json, true);

        if(!empty($details['ip'])) {

            return $details['ip'];

        } else {

            $url_to_exch_code = "https://freegeoip.net/json/";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url_to_exch_code);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);

            $details = json_decode($json, true);

            if(!empty($details['ip'])) {

                return $details['ip'];

            } else {

                $url_to_exch_code = "https://geoip.nekudo.com/api/json";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url_to_exch_code);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $json = curl_exec($ch);
                curl_close($ch);

                $details = json_decode($json, true);

                if(!empty($details['ip'])) {

                    return $details['ip'];

                } else {

                    return false;

                }
            }
        }
    }


}

