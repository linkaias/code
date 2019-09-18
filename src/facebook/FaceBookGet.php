<?php
namespace lkcodes\Mycode\facebook;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookApp;
use Facebook\FacebookBatchRequest;
use Facebook\GraphNodes\GraphNodeFactory;


class FaceBookGet {
    public $client ;
    public $token;
    public function __construct($level=false)
    {
        $_fb = new FaceBook();
        $this->token=$_fb->token;
        if($level===false){
            $fb = new \Facebook\Facebook([
                'app_id' => $_fb->app_id,
                'app_secret' => $_fb->app_secret,
                'default_graph_version' => 'v3.3',
            ]);
        }else{
            $fb = new \Facebook\Facebook([
                'app_id' => $_fb->app_id,
                'app_secret' => $_fb->app_secret,
                'default_graph_version' => $_fb->default_graph_version,
            ]);
        }

        $fb->setDefaultAccessToken($this->token);
        $this->client = $fb;
    }

    /**
     * curl GET API
     * @param string $_url  //前缀 版本号..
     * @param string $url   需要请求url
     * @return bool|mixed
     */
    public function get($url='',$_url =''){
        $fb = new FaceBook();
        $token = $fb->token;
        $curl = new Curl();
        $_url = $_url =='' ? "https://graph.facebook.com/".$fb->default_graph_version."/" : $_url;
        if(strpos($url, '?') !== false) {
            $link = $_url.$url.'&access_token='.$token;
        }else{
            $link = $_url.$url.'?access_token='.$token;
        }
        //dump($link);die;
        $res = $curl->get($link,true);
        return $res;
    }

    public function _get($url){
        $curl = new Curl();
        $res = $curl->get($url,true);
        return $res;
    }

    /**
     * facebook get  GraphEdge
     * @param string $url  eq：220398642106724/adaccountcreationrequest
     * @return array|bool
     * @throws FacebookSDKException
     */
    public function FbGet($url=''){
        try {
            $response = $this->client->get($url);
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        //循环调用所有页面数据
        // Page 1
        $datas=[];

        $feedEdge = $response->getGraphEdge();
        if($feedEdge){
            foreach ($feedEdge as $status) {
                $res =$status->asArray();
                $datas[] = $res;
            }
        }else{
            return false;
        }
        // Page 2 (next 5 results)
        $nextFeed = $this->client->next($feedEdge);
        if($nextFeed){
            foreach ($nextFeed as $status) {
                $res =$status->asArray();
                $datas[] = $res;
            }
            //循环调用
            static $tem_obj;
            $tem_obj= $nextFeed;
            for ($i=0;$i<6;$i++){
                $tem_obj = $this->client->next($tem_obj);
                if($tem_obj){
                    foreach ($tem_obj as $status) {
                        $res =$status->asArray();
                        $datas[] = $res;
                    }
                }else{
                    break;
                }

            }

        }
        return $datas;

    }

    /**
     * facebook get  GraphEdgeNode
     * @param string $url
     * @return array|bool
     * @throws FacebookSDKException
     */
    public function FbGetNode($url=''){
        try {
            $response = $this->client->get($url);
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        $feedEdge = $response->getGraphNode();
        $res =$feedEdge->asArray();
        return $res ? $res : false;
    }




    public function EtagGet($url='',$Etag='',$getGtag=true){
        $_fb = new FaceBook();
        $fb = new \Facebook\Facebook(['app_id'=>$_fb->app_id,'app_secret'=>$_fb->app_secret,'default_graph_version'=>$_fb->default_graph_version]);
        $request = $fb->request('GET', $url);
        $request->setAccessToken($this->token);
        $request->setETag($Etag);

        try {
            $response = $fb->getClient()->sendRequest($request);

        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            //echo 'Graph returned an error: ' . $e->getMessage();
            //exit;
            return false;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            //echo 'Facebook SDK returned an error: ' . $e->getMessage();
            //exit;
            return false;
        }
        //没有改变
        if($response->getHttpStatusCode() == 304){
            return true;
        }
        if($getGtag){
            $data['etag'] = $response->getETag();
        }
        $res = $response->getDecodedBody();
        //$graphNode = $response->getGraphNode();
        $data['data'] = $res;
        return $data;


    }



}



