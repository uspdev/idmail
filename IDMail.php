<?php

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class IDMail
{
    var $client;

    function __construct()
    {
        $dotenv = Dotenv::create(__DIR__);
        $dotenv->load();

        $this->client = $this->login();
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
        $dom = new DOMDocument();
        $dom->loadHTML($response->getBody());
        $xpath = new DOMXpath($dom);

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

    function extract_email($json)
    {
        if ($json->response == true) {
            $last = ['date' => 0, 'email' => '',];
            foreach ($json->result as $email => $dados) {
                if ($dados->tipo == "Pessoal" or $dados->tipo == "Secundaria") {
                    $user = explode("@", $email);
                    if ($user[1] == "ime.usp.br") {
                        $date = strtotime($dados->dtainival);
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

    function id_get_emails($nusp)
    {
        $response = $this->client->get("https://id-admin.internuvem.usp.br/sybase/json/$nusp/emails/");

        return $response->getBody();
    }
}

?>
