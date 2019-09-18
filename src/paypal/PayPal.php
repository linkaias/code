<?php
/**
 * 封装合作伙伴API 请求类
 * ************************************
 * ************************************
 */
namespace lkcodes\Mycode\paypal;

class PayPal
{
    //Client ID
    protected $username = 'AYVVLrufsjafk74ddsZQ9MFYoJZNnsMMhz0HU-9nhqm7spU0WQkqC2Gigf3tdm6AT0wa6IX3pUE5oDKU';
    //Secret
    protected $password = 'EH5lQOeJG8CXW_EXu42g3syCgHHgnyk6Q-Jbv2H5ILdTQtA2jNlmr1M8dQ8KyUDrEXx80AAAC1a0q-9J';
    //商家账号
    protected $partner_id ='7FW776SHLL958';
    public $url = 'https://api.sandbox.paypal.com';
    public $version = 'v1';
    public $token;

    public function __construct()
    {
        $this->init();
    }

    /**
     * @auth
     * @menu
     * 应用初始化
     */
    protected function init()
    {
        //判断获取token
        $is_setToken = cookie('paypal_token');
        if (!$is_setToken) {
            $token = $this->getToken();
            if ($token) {
                $temp['access_token'] = $token->access_token;
                $temp['addtime'] = time();
                $this->token = $token->access_token;
                cookie('paypal_token', $temp, 600);
            } else {
                $this->error_msg = '获取token失败！';
            }
        } else {
            $this->token = $is_setToken['access_token'];
        }
    }

    /**
     * 获取token
     * @return mixed|string
     */
    protected function getToken()
    {
        $site = 'oauth2/token';
        $url = $this->getUrl($site);
        $client = new CurlDis();
        $client->client->setBasicAuthentication($this->username, $this->password);
        $client->client->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $client->client->post($url, ['grant_type' => 'client_credentials']);
        if ($client->client->error) {
            return $client->client->getErrorMessage();
        } else {
            return json_decode($client->client->response);
        }
    }

    /**
     * 返回商家授权链接
     * @param array $param
     * @return mixed
     */
    public function getReturnUrl($param = [])
    {

        $site = 'customer/partner-referrals';
        $url = $this->getUrl($site);
        $client = new CurlDis();
        $client->client->setHeader('Authorization', 'Bearer ' . $this->token);
        $client->client->setHeader('Content-Type', 'application/json');
        $data = '{
    "customer_data": {
      "customer_type": "MERCHANT",
      "partner_specific_identifiers": [{
        "type": "TRACKING_ID",
        "value": "1506721845"
      }]
    },
    "requested_capabilities": [{
      "capability": "API_INTEGRATION",
      "api_integration_preference": {
        "partner_id": "'.$this->partner_id.'",
        "rest_api_integration": {
          "integration_method": "PAYPAL",
          "integration_type": "THIRD_PARTY"
        },
        "rest_third_party_details": {
          "partner_client_id": "'.$this->username.'",
          "feature_list": [
            "PAYMENT",
            "REFUND",
            "READ_SELLER_DISPUTE",
            "UPDATE_SELLER_DISPUTE"
          ]
        }
      }
    }],
    "web_experience_preference": {
      "partner_logo_url": "https://ysn.uiucode.cn/Public/Admin/images/logo.png",
      "return_url": "https://dispute.uiucode.cn/admin.html#/paypal/public_controller/returnData",
      "action_renewal_url": "https://dispute.summblog.cn/admin.php/public_controller/paypalaction"
    },
    "collected_consents": [{
      "type": "SHARE_DATA_CONSENT",
      "granted": true
    }],
    "products": [
      "EXPRESS_CHECKOUT"
    ]
  }';

        $client->client->post($url, $data);
        return json_decode($client->client->response);
    }

    /**
     * @param $site
     * @return string
     */
    protected function getUrl($site)
    {
        return $this->url . '/' . $this->version . '/' . $site;
    }

    /**
     * get paypal Auth token
     * @param string $payer_id
     * @param string $iss
     * @return string
     */
    public function getPayPalAuth($payer_id = '7XXZVRHZXKC9J', $iss = '')
    {
        $iss = $iss ? $iss :$this->username;
        $t_1 = base64_encode("{\"alg\":\"none\"}");
        $t_2 = base64_encode("{\"payer_id\":$payer_id,\"iss\":$iss}");
        $token = $t_1 . "." . $t_2 . ".";
        return $token;
    }

    /**
     * get disputes by payer_id
     * @param $payer_id
     * @return mixed
     */
    public function getDisputeList($payer_id)
    {
        $auth = $this->getPayPalAuth($payer_id);
        $site = 'customer/disputes';
        $url = $this->getUrl($site);
        $client = new CurlDis();
        $client->client->setHeader('Authorization', 'Bearer ' . $this->token);
        $client->client->setHeader('Content-Type', 'application/json');
        $client->client->setHeader('PayPal-Auth-Assertion', $auth);
        $client->client->get($url);
        return json_decode($client->client->response);
    }

    /**
     * 获取争议详情
     * @param $pp_id
     * @param $pay_id
     * @return mixed
     */
    public function getDisputeView($pp_id, $pay_id)
    {
        $auth = $this->getPayPalAuth($pay_id);
        $site = 'customer/disputes/' . $pp_id;
        $url = $this->getUrl($site);
        $client = new CurlDis();
        $client->client->setHeader('Authorization', 'Bearer ' . $this->token);
        $client->client->setHeader('Content-Type', 'application/json');
        $client->client->setHeader('PayPal-Auth-Assertion', $auth);
        $client->client->get($url);
        return json_decode($client->client->response);
    }

    /**
     * 发送争议消息
     * @param $dispute_id
     * @param $msg
     * @param $pay_id
     * @return mixed
     */
    public function sendMsg($dispute_id,$msg,$pay_id)
    {
        $auth = $this->getPayPalAuth($pay_id);
        $site = 'customer/disputes/' . $dispute_id.'/send-message';
        $url = $this->getUrl($site);
        $client = new CurlDis();
        $client->client->setHeader('Authorization', 'Bearer ' . $this->token);
        $client->client->setHeader('Content-Type', 'application/json');
        $client->client->setHeader('PayPal-Auth-Assertion', $auth);
        $data ='{
	        "message": "'.$msg.'"
        }';
        $client->client->post($url,$data);
        return json_decode($client->client->response);

    }

    /**
     * 接受争议赔偿
     * @param $pay_id
     * @param $dispute_id
     * @param string $msg
     * @return mixed
     */
    public function acceptClaim($pay_id,$dispute_id,$msg='accept')
    {
        $auth = $this->getPayPalAuth($pay_id);
        $site = 'customer/disputes/' . $dispute_id.'/accept-claim';
        $url = $this->getUrl($site);
        $client = new CurlDis();
        $client->client->setHeader('Authorization', 'Bearer ' . $this->token);
        $client->client->setHeader('Content-Type', 'application/json');
        $client->client->setHeader('PayPal-Auth-Assertion', $auth);
        //备注
        $data ='{
	        "note": "'.$msg.'"
        }';
        $client->client->post($url,$data);
        return json_decode($client->client->response);
    }

    /**
     * 争议升级
     * @param $pay_id
     * @param $dispute_id
     * @param string $msg
     * @return mixed
     */
    public function escalate($pay_id , $dispute_id,$msg='Escalating to PayPal claim for resolution')
    {
        $auth = $this->getPayPalAuth($pay_id);
        $site = 'customer/disputes/' . $dispute_id.'/escalate';
        $url = $this->getUrl($site);
        $client = new CurlDis();
        $client->client->setHeader('Authorization', 'Bearer ' . $this->token);
        $client->client->setHeader('Content-Type', 'application/json');
        $client->client->setHeader('PayPal-Auth-Assertion', $auth);
        //备注
        $data ='{
	        "note": "'.$msg.'"
        }';
        $client->client->post($url,$data);
        return json_decode($client->client->response);
    }

    /**
     * 报价解决争议
     * @param $pay_id
     * @param $dispute_id
     * @param $data
     * @return mixed
     */
    public function makeOffer($pay_id,$dispute_id,$data)
    {
        $auth = $this->getPayPalAuth($pay_id);
        $site = 'customer/disputes/' . $dispute_id.'/make-offer';
        $url = $this->getUrl($site);
        $client = new CurlDis();
        $client->client->setHeader('Authorization', 'Bearer ' . $this->token);
        $client->client->setHeader('Content-Type', 'application/json');
        $client->client->setHeader('PayPal-Auth-Assertion', $auth);
        $data['note'] = $data['note']? $data['note']:'ok';
            //直接退款
            if($data['offer_type'] == 'REFUND'){
                $return='{
          "note": "'.$data['note'].'",
          "offer_amount": {
                "currency_code": "'.$data['amoun_code'].'",
                "value": "'.$data['amoun_val'].'"
            },
          "offer_type": "REFUND"
        }';
                //顾客先退货
            }elseif($data['offer_type'] =='REFUND_WITH_RETURN'){

                //退款并补发商品
            }elseif($data['offer_type'] =='REFUND_WITH_REPLACEMENT'){
                $return='{
          "note": "'.$data['note'].'",
          "offer_amount": {
                "currency_code": "'.$data['amoun_code'].'",
                "value": "'.$data['amoun_val'].'"
            },
          "offer_type": "REFUND_WITH_REPLACEMENT"
         
        }';
                //不退款仅补发商品
            }elseif($data['offer_type'] =='REPLACEMENT_WITHOUT_REFUND'){
                $return='{
          "note": "'.$data['note'].'",
          "offer_type": "REPLACEMENT_WITHOUT_REFUND"
          }';
            }

        $client->client->post($url,$return);
        return json_decode($client->client->response);
    }

}