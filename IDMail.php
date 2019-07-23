<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class IDMail
{
    var $client;

    function __construct($cache_mode)
    {
        $this->client = $this->login();

        if ($cache_mode == "all") {
            $this->update_cache();
        }
    }

    private function login()
    {
        $login = getenv('LOGIN');
        $pass = getenv('PASSWORD');

        $cookies = new CookieJar();
        $client = new Client(['cookies' => $cookies]);
        $response = $client->get("https://id-admin.internuvem.usp.br/portal/");

        # get jsessionid
        $cookie = $cookies->getCookieByName("JSESSIONID");
        $jsessionid = $cookie->toArray()['Value'];

        # generate SAML request
        $response = $client->post("https://idpcafe.usp.br/idp/profile/SAML2/Redirect/SSO;jsessionid=$jsessionid?execution=e1s1", [
            'form_params' => [
                'j_username' => $login,
                'j_password' => $pass,
                '_eventId_proceed' => '',
            ]
        ]);

        # extract SAML data from response
        $dom = new \DOMDocument();
        $dom->loadHTML($response->getBody());
        $xpath = new \DOMXpath($dom);

        $relaystate = $xpath->query("//html/body/form/div/input[@name='RelayState']/@value")[0]->textContent;
        $samlresponse = $xpath->query("//html/body/form/div/input[@name='SAMLResponse']/@value")[0]->textContent;

        # SAML authentication
        $response = $client->post("https://id-admin.internuvem.usp.br/Shibboleth.sso/SAML2/POST", [
            'form_params' => [
                'RelayState' => $relaystate,
                'SAMLResponse' => $samlresponse,
            ]
        ]);

        return $client;
    }

    private function update_cache()
    {
        $response = $this->client->get("https://id-admin.internuvem.usp.br/sybase/json/".getenv('LOGIN')."/all_emails/");
        file_put_contents(getenv('MAIL_CACHE')."/all_emails.json", $response->getBody());
    }

    function id_get_emails($nusp)
    {
        $response = $this->client->get("https://id-admin.internuvem.usp.br/sybase/json/$nusp/emails/");
        file_put_contents(getenv('MAIL_CACHE')."/".$nusp.".json", $response->getBody());

        return $response->getBody();
    }

    static function extract_email($json, $domain, $type)
    {
        if ($json->response == true) {
            $last = ['date' => 0, 'email' => '',];
            foreach ($json->result as $email => $data) {
                if (in_array($data->tipo, $type)) {
                    $user = explode("@", $email);
                    if ($user[1] == $domain) {
                        $date = strtotime($data->dtainival);
                        if ($last['date'] < $date) {
                            $last['date'] = $date;
                            $last['email'] = $email;
                        }
                    }
                }
            }
        }

        return $last['email'];
    }

    static function list_emails($json, $domain, $type)
    {
        $emails = [];
        if ($json->response == true) {
            foreach ($json->result as $email => $data) {
                if (in_array($data->tipo, $type)) {
                    $emails[] = ['email' => $email, 'name' => $data->nomaptema];
                }
            }
        }

        return $emails;
    }

    static function get_cache($cache_file) {
        if (!file_exists($cache_file)) {
            return null;
        }

        $delta = (time() - filemtime($cache_file))/86400;
        if ($delta > 1) {
            return null;
        }

        return file_get_contents($cache_file);
    }

    static function cache_get_emails($nusp) {
        $cache_file = getenv('MAIL_CACHE')."/".$nusp.".json";
        return IDMail::get_cache($cache_file);
    }

    static function cache_find_email($nusp, $type)
    {
        $cache_file = getenv('MAIL_CACHE')."/all_emails.json";
        $cache = IDMail::get_cache($cache_file);
        if (!$cache) {
            return null;
        }

        $json = json_decode($cache);
        if ($json->response == true) {
            foreach ($json->result as $email => $data) {
                if (in_array($data->tipo, $type) and $data->codpes == $nusp) {
                    return $email;
                }
            }
        }

        return null;
    }
}

?>
