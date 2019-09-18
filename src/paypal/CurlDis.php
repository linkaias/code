<?php

namespace lkcodes\Mycode\paypal;

use Curl\Curl;

class CurlDis
{
    public  $client ;
    public function __construct()
    {
        $client = new Curl();
        $this->client= $client;
    }

    /**
     * @param $url
     * @return mixed|string
     */
    public function get($url)
    {
        $this->client->get($url);
        if($this->client->error){
            $this->client->close();
            return $this->client->getErrorMessage();
        }else{
            $this->client->close();
            return json_decode($this->client->response);
        }
    }

    public function test(){
        echo 'test paypal';
    }

}