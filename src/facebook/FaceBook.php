<?php
namespace lkcodes\Mycode\facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookApp;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use FacebookAds\Api;
use FacebookAds\Cursor;
use FacebookAds\Object\AdCreative;
use FacebookAds\Object\AdsInsights;
use FacebookAds\Object\AdVideo;
use FacebookAds\Object\Fields\AdAccountFields;
use FacebookAds\Object\Fields\AdImageFields;
use FacebookAds\Object\Fields\AdPreviewFields;
use FacebookAds\Object\Fields\AdsInsightsFields;
use FacebookAds\Object\Fields\AdVideoFields;
use FacebookAds\Object\Fields\BusinessFields;
use FacebookAds\Object\Fields\CustomAudienceFields;
use FacebookAds\Object\Fields\InsightsResultFields;
use FacebookAds\Object\Fields\OracleTransactionFields;
use FacebookAds\Object\User;
use FacebookAds\Object\Values\AdPreviewAdFormatValues;
use FacebookAds\Object\Campaign;
use FacebookAds\Object\Fields\AdSetFields;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Fields\AdFields;
use FacebookAds\Object\AdSet;
use FacebookAds\Object\Ad;
use FacebookAds\Object\Fields\AdCreativeFields;
use FacebookAds\Object\Business;
use FacebookAds\Object\Values\ArchivableCrudObjectEffectiveStatuses;
use FacebookAds\Object\Values\InsightsResultDatePresetValues;
use Think\Page;

/**
 * FaceBook Api init
 * @return \Lib\FaceBook
 */
class FaceBook
{

    public $business_id;   //管理平台id
    public $token;
    public $app_id;
    public $app_secret;
    public $default_graph_version;


    /**
     * FaceBook constructor.
     * @param int $app_id =0 默认第一个App =1第二个...
     */
    public function __construct()
    {
        $cache = new Cache();
        //随机调用App
        $num = $cache->get('app_id_cache');
        if(!$num){
            $m = D('core_config');
            $app_id = $m->query("select * from core_config where `name` like 'app_id'");
            $app_id = $app_id[0]['value'];
            $app_id = explode(',',$app_id);
            $num = count($app_id) -1;
            $cache->set('app_id_cache',$num,36000);
        }
        $num = rand(0,$num);
        $this->FaceBookInit($num);
        //$this->FaceBookInit(2);
    }

    /**
     * 初始化facebook
     */
    function FaceBookInit($app_num)
    {
        $m = D('core_config');
        $business_id = $m->query("select `value` from core_config where `name` like 'business_id'");
        $this->business_id = $business_id[0]['value'];

        $app_id = $m->query("select `value` from core_config where `name` like 'app_id'");
        $app_id = $app_id[0]['value'];
        $app_id = explode(',',$app_id);
        $app_id = isset($app_id[$app_num]) ? $app_id[$app_num] : $app_id[0] ;

        $app_secret = $m->query("select `value` from core_config where `name` like 'app_secret'");
        $app_secret = $app_secret[0]['value'];
        $app_secret = explode(',',$app_secret);
        $app_secret = isset($app_secret[$app_num]) ? $app_secret[$app_num] : $app_secret[0];

        $access_token = $m->query("select `value` from core_config where `name` like 'access_token'");
        $access_token = $access_token[0]['value'];
        $access_token = explode(',',$access_token);
        $access_token = isset($access_token[$app_num]) ? $access_token[$app_num] : $access_token[0];

        $default_graph_version = $m->query("select `value` from core_config where `name` like 'default_graph_version'");
        $default_graph_version = $default_graph_version[0]['value'];
        $default_graph_version = explode(',',$default_graph_version);
        $default_graph_version = isset($default_graph_version[$app_num]) ? $default_graph_version[$app_num] : $default_graph_version[0];

        /*$app_id = '1872456469489254';
        $app_secret ='5ce0644a43abb6fb265de93b9d960e5a';
        $access_token ='EAAamZCT64pmYBAN3VVB2ICJk2e1ZCXoX1ZAN1GW2XhG623udQAnAnGu6yD886lpZCHZAmiQaSOfI26VlPQeV2jYn94dgria6vACPPfpNgQjqeSuuNYMNpVmrrneIroNUEZBMeVe09tciSrwFHgiKdrXpt78pSPoYyWjjYiydHZBJLWrv4LLRiDV';*/


    //管理员token       //EAALvgBr8xJQBAA4vWiFTN3CNJzaitGN7KBb29pZArLkhoq6x1eIxsSBnJDdfHkrcIUFgh4sEI9msmcvepswwPCKQhWZBQkDauJJECukFoMhz0P2FR6ew0Gu7pFB74hjbWuZCHz885HeUYfFwhqoUuBAVhY8JWQWDA8lgDCn6ME3A8m8QKLx

        $this->token = $access_token;
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->default_graph_version = $default_graph_version;
        Api::init($app_id, $app_secret, $access_token);
        Cursor::setDefaultUseImplicitFetch(true);   //在getResponse()方法之前使用

    }



    /**
     * 通过广告id获取预览
     * @param $id
     * @return mixed
     */
    function getAdById($id)
    {
        $this->__construct();
        $creative = new AdCreative($id);
        $ad = $creative->getPreviews(array(), array(
            AdPreviewFields::AD_FORMAT => AdPreviewAdFormatValues::DESKTOP_FEED_STANDARD,
        ));
        $ad = $ad->getResponse()->getContent();
        return $ad['data'][0]['body'];
    }

    /**
     * 获取广告系列id (old)
     * @return array|bool
     */
    function getCampaignsId($acc_id ='act_259488884838951' ){

        $cam = new AdAccount($acc_id);
        $preme = array(
        );
        $campaigns= $cam->getCampaigns(array(
            CampaignFields::ID,
            CampaignFields::NAME,
            CampaignFields::STATUS,

        ),$preme);

        if(count($campaigns)>0){
            $data = array();
            foreach ($campaigns as $campaign){
                if($campaign->status =='ACTIVE'){
                    $data[] = $campaign->id;
                }
            }
            return $data;
        }else{
            return false;
        }
    }

    /**
     * 通过广告系列获取广告组(old)
     * @param $campaign_id
     * @return array|bool
     */
    function getAdSetsId($campaign_id){
        $campaign = new Campaign($campaign_id);
        $adsets = $campaign->getAdSets(array(
            AdSetFields::ID,
            AdSetFields::NAME,
            AdSetFields::STATUS,
        ));
       if(count($adsets)>0){
           $data = array();
           foreach ($adsets as $adset){
               if($adset->status == 'ACTIVE'){
                   $data[] = $adset->id;
               }
           }
           return $data;
       }else{
           return false;
       }

    }

    /**
     * 通过广告组id获取广告(old)
     * @param $ad_sets_id
     * @return array|bool
     */
    function getAdsId($ad_sets_id){

        $adset = new AdSet($ad_sets_id);
        $ads = $adset->getAds(array(
            AdFields::ID,
            AdFields::STATUS,
            AdFields::CONFIGURED_STATUS,
            AdFields::EFFECTIVE_STATUS,

        ));
        if(count($ads)>0){
            $data = array();
            foreach ($ads as $ad){
                if($ad->status == 'ACTIVE'&& $ad->effective_status !='DISAPPROVED'){
                    $data[] = $ad->id;
                }
            }
            return $data;
        }else{
            return false;
        }
    }

    /**
     * 获取所有广告信息
     * @return array
     */
    function getAllAds($ads = array()){
        $adres = [];
        if($ads){
            foreach ($ads as $ad){
                $adarr= $this->getAdById($ad['ad_id']);
                $ad['ad_content']= $adarr;
                $adres[]=$ad;
            }
        }
        return $adres;
    }

    /**
     * 判断非法关键字获取所有广告信息
     */
    function getAllAdBody($acc_id){
        $adres = array();
        $ads = $this->getAdIdsByDatabase($acc_id);
        if(!empty($ads)){
            foreach ($ads as $ad_id ){
                $ad= $this->getAdBody($ad_id);
                if(!empty($ad)){
                    $adres[]=array(
                        'ad_id'=>$ad_id,
                        'text'=>isset($ad['text'])? $ad['text']:'',
                        'title'=>isset($ad['title'])? $ad['title']:'',
                        'description'=>isset($ad['description'])? $ad['description']:'',
                    );
                }
            }
        }
        return $adres;
    }

    /**
     * 获取广告的关键词信息
     * @param $ad_id
     * @return array
     */
    function getAdBody($ad_id){
        $ad = new Ad($ad_id);
        $ad =$ad->getAdCreatives(array(
            AdCreativeFields::BODY,
            AdCreativeFields::OBJECT_STORY_SPEC,
        ));

        $adcontent = $ad->getResponse()->getContent();
        //dump($adcontent);die;
        $data=array();
        $adbody = $adcontent['data'][0]['body'];
        $data['text']= $adbody;
        $adtitle=$adcontent['data'][0]['object_story_spec'];
        if(!empty($adtitle['link_data'])){
            //图片广告
            $child =$adtitle['link_data']['child_attachments'];
            $title = $des = $url ='';
            $url = $adtitle['link_data']['link'].',';
            if(!empty($child)){
                //多图广告
                foreach ($child as $v){
                    $title .= $v['name'].',';
                    $des .= $v['description'].',';
                    $url .= $v['link'].',';
                }
            }else{
                //单图广告
                $title .= $adtitle['link_data']['name'];
                $des .= $adtitle['link_data']['description'];
            }
            //$data['title']= $adtitle['link_data']['child_attachments'][0]['name'];
            $data['title']= $title;
            $data['description']= $des;
            $data['url']= $url;

        }elseif(!empty($adtitle['video_data'])){
            //视频广告
            $data['title']= $adtitle['video_data']['title'];
            $data['url']= $adtitle['video_data']['call_to_action']['value']['link'];
            $data['description']= $adtitle['video_data']['link_description'];
        }elseif(!empty($adtitle['template_data'])){
            //商品链接
            $data['url']= $adtitle['template_data']['link'];
            //暂未处理
        }
       return $data;
    }

    function getAdUrlById($ad_id)
    {
        $ad = new Ad($ad_id);
        $ad =$ad->getAdCreatives(array(
            AdCreativeFields::BODY,
            AdCreativeFields::OBJECT_STORY_SPEC,
        ));
        $adcontent = $ad->getResponse()->getContent();
        /**
         * 推广URL
         */
        $adtitle=$adcontent['data'][0]['object_story_spec'];
        if(!empty($adtitle['link_data'])){
            //图片广告
            $child =$adtitle['link_data']['child_attachments'];
            $url = $adtitle['link_data']['link'].',';
            if(!empty($child)){
                //多图广告
                foreach ($child as $v){
                    $url .= $v['link'].',';
                }
            }else{
                //单图广告
            }

        }elseif(!empty($adtitle['video_data'])){
            //视频广告

            $url= $adtitle['video_data']['call_to_action']['value']['link'];
        }elseif(!empty($adtitle['template_data'])){
            //商品链接
            $url= $adtitle['template_data']['link'];
            //暂未处理
        }
        $url = isset($url) ? trim($url,',') :false;

        return $url;
    }

    /**
     * 获取广告图片hash等
     * @param $ad_id
     * @return bool|string
     */
    function getAdDataById($ad_id)
    {
        $ad = new Ad($ad_id);
        $ad =$ad->getAdCreatives(array(
            AdCreativeFields::BODY,
            AdCreativeFields::OBJECT_STORY_SPEC,
        ));
        $adcontent = $ad->getResponse()->getContent();

        /**
         * 推广URL
         */
        $adtitle=$adcontent['data'][0]['object_story_spec'];
        if(!empty($adtitle['link_data'])){
            //图片广告
            $child =$adtitle['link_data']['child_attachments'];
            $url = $adtitle['link_data']['link'].',';
            if(!empty($child)){
                //多图广告
                foreach ($child as $v){
                    $url .= $v['link'].',';
                }
            }else{
                //单图广告
            }

        }elseif(!empty($adtitle['video_data'])){
            //视频广告
            $video_id = $adtitle['video_data']['video_id'];
            $url= $adtitle['video_data']['call_to_action']['value']['link'];
        }elseif(!empty($adtitle['template_data'])){
            //商品链接
            $url= $adtitle['template_data']['link'];
            //暂未处理
        }
        $url = isset($url) ? trim($url,',') :false;
        /**
         * 文本内容
         */
        //暂取body
        $text = $adcontent['data'][0]['body'];


        /**
         * 图片Hash链接
         */
        $hases =$adcontent['data'][0]['object_story_spec'];
        //图片广告
        $_hases='';
        if(isset($hases['link_data'])){
            //多图广告
            if(isset($hases['link_data']['child_attachments'])){
                $__hash='';
                foreach ($hases['link_data']['child_attachments'] as $_hash){
                    $__hash .=$_hash['image_hash'].',';
                }
                $_hases =trim($__hash,',');
            }else{
                //单图广告
                $_hases =$hases['link_data']['image_hash'];
            }
        }

        $data=[
            'text'=>$text ? $text : false,
            'url'=>$url,
            'hash' => $_hases? $_hases :false,
            'video_id' =>$video_id ? $video_id :false
        ];

        return $data;
    }


    /**
     * 根据广告账户获取昨日花费金额
     * @return mixed
     */
    function getSpendByAccountId($acc_id,$date='yesterday'){
        $account = new AdAccount($acc_id);
        $params = array(
            'level' => 'account',
            'date_preset' => $date,
            'fields' => 'spend',
        );
        $spend =$account->getInsights(array('account_name','spend','account_id'
        ), $params)->getResponse()->getContent();
        $new =$spend['data'][0];
        return $new;
    }

    /**
     * 获取广告账户每个时间段内花费
     * @param $acc_id
     * @param $data_range
     * @param bool $is_two
     * @return mixed
     */
    function getTimeSpend($acc_id,$data_range,$is_two=false){
        if($is_two){
            $account = new AdAccount($acc_id);
            $params = array(
                'level' => 'account',
                //'time_range' =>['since'=>'2019-05-03','until'=>'2019-05-10'],  //指定时间范围
                //'time_ranges' => [['since'=>'2019-05-03','until'=>'2019-05-10'],['since'=>'2019-04-03','until'=>'2019-05-01']],
                'time_ranges' => $data_range,
                'fields' => 'spend',
            );
            $spend =$account->getInsights(array('account_name','spend','account_id'
            ), $params)->getResponse()->getContent();
            return $spend['data'];

        }else{
            $account = new AdAccount($acc_id);
            $params = array(
                'level' => 'account',
                'time_range' =>$data_range,  //指定时间范围
                'fields' => 'spend',
            );
            $spend =$account->getInsights(array('account_name','spend','account_id'
            ), $params)->getResponse()->getContent();

            $new =$spend['data'][0];
            return $new;
        }


    }

    /**
     * 获取广告组预算
     * @return mixed
     */
    function getBudget($adset_id){
        $adset = new AdSet($adset_id);
        $budget =$adset->read(array(
            AdSetFields::NAME,
            AdSetFields::DAILY_BUDGET,
        ));

        $budgets = $budget->getData();
        $bud= $budgets['daily_budget'];

        $new =substr($bud,0,strlen($bud)-2);
        return (intval($new));

    }

    /**
     * 通过广告组id获取当天预算
     * @return int
     */
    function getBudgetByCurl($adset_id = '23843031213020231'){
        $get = new FaceBookGet();
        $res =$get->get('https://graph.facebook.com/v3.3/'.$adset_id.'/?fields=daily_budget');
        $buget = $res['daily_budget'];
        return intval(substr($buget,0,strlen($buget)-2));
    }

    /**
     * 获取账户每日预算总额
     * @return int|mixed
     */
    function getAccountBudget($acc_id,$sleep=false){
        $budget=0;
        $adsets =$this->getAllContentId($acc_id,false,true);
        if(!empty($adsets)){
            foreach ($adsets as $adset){
                $budgets = $this->getBudget($adset);
                if($budgets){
                    $budget +=$budgets;
                }
            }
        }
        return $budget;
    }

    /**
     * 获取账户每日预算总额 通过图谱APi
     * @param $acc_id
     * @return int
     */
    function getAccountBudgetByCurl($acc_id){

        $budget=0;
        $adsets =$this->getAllContentId($acc_id,false,true);
        if(!empty($adsets)){
            foreach ($adsets as $adset){
                $budgets = $this->getBudgetByCurl($adset);
                if($budgets){
                    $budget +=$budgets;
                }
            }
        }
        return $budget;
    }

    /**
     * 通过管理平台id 获取所有广告账户
     * @return mixed
     */
    function getAllAccounts(){
        $account =new Business($this->business_id);
        $filed = array(
            'limit'=>'300',
        );
        Cursor::setDefaultUseImplicitFetch(true);   //在getResponse()方法之前使用
        $acs= $account->getOwnedAdAccounts(array(
            AdAccountFields::ACCOUNT_STATUS,
            AdAccountFields::ACCOUNT_ID,
            AdAccountFields::AGE,'name',
            AdAccountFields::CREATED_TIME,
            AdAccountFields::DISABLE_REASON,

        ),$filed);
        $arr =[];
        foreach ($acs as $account){
            $_data =$account->getData();
            $tem['account_status']=$_data['account_status'];
            $tem['account_id']=$_data['account_id'];
            $tem['age']=$_data['age'];
            $tem['name']=$_data['name'];
            $tem['created_time']=$_data['created_time'];
            $tem['disable_reason']=$_data['disable_reason'];
            $tem['id']=$_data['id'];
            $arr[] = $tem;
        }
        return $arr;
    }

    /**
     * 每日同步使用
     * @return array
     *
     */
    function getAllAccountsMuch(){
        $account =new Business($this->business_id);
        $filed = array(
            'limit'=>'300',
        );
        Cursor::setDefaultUseImplicitFetch(true);   //在getResponse()方法之前使用
        $acs= $account->getOwnedAdAccounts(array(
            AdAccountFields::ACCOUNT_STATUS,
            AdAccountFields::ACCOUNT_ID,
            AdAccountFields::AGE,'name',
            AdAccountFields::CREATED_TIME,
            AdAccountFields::DISABLE_REASON,
            AdAccountFields::AMOUNT_SPENT,
            AdAccountFields::SPEND_CAP,
            AdAccountFields::BALANCE,
            AdAccountFields::CURRENCY,
            AdAccountFields::TIMEZONE_NAME,
        ),$filed);
        $arr =[];
        foreach ($acs as $account){
            $_data =$account->getData();
            $tem['account_id']=$_data['account_id'];
            $tem['account_name']=$_data['name'];
            $tem['status']=$_data['account_status'];
            $tem['created_time']=$_data['created_time'];
            $tem['age']=$_data['age'];
            $tem['amount_spent']=$_data['amount_spent'];
            $tem['spend_cap']=$_data['spend_cap'];
            $tem['balance']=$_data['balance'];
            $tem['currency']=$_data['currency'];
            $tem['disable_reason']=$_data['disable_reason'];
            $tem['timezone_name']=$_data['timezone_name'];
            $tem['addtime']=time();
            $arr[] = $tem;
        }
        return $arr;
    }

    /**
     * 获取BM下所有广告账户
     * @return array|bool
     * @throws FacebookSDKException
     */
    function getAllAccountsNew(){
        $fbget = new FaceBookGet();
        $res = $fbget->FbGet($this->business_id.'/owned_ad_accounts?fields=account_status,account_id,age,name,created_time,disable_reason&limit=200');
        if($res){
            $arr =[];
            foreach ($res as $account){
                $tem['account_status']=$account['account_status'];
                $tem['account_id']=$account['account_id'];
                $tem['age']=$account['age'];
                $tem['name']=$account['name'];
                $tem['created_time']=$account['created_time']->getTimestamp();;
                $tem['disable_reason']=$account['disable_reason'];
                $tem['id']=$account['id'];
                $arr[] = $tem;
            }
            return $arr;
        }else{
            return false;
        }
    }

    /**
     * 获取所有账户分页信息by databases
     * @return array
     */
    function getAccountsLimit($qty = 10){
        if(is_admin_login()){
            //搜索
            $whe = session('search_oe');

            if($whe['chinese_legal_entity_name'] == ''){
                unset($whe['chinese_legal_entity_name']);
            }
            if($whe['status'] ==''){
                unset($whe['status']);
            }


            $d= D('f_advertiser');
            $count =$d->where($whe)->order('id desc')->count();
            $page = new Page($count,$qty);

            $data = $d
                ->where($whe)
                ->order('id desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数
            $page = $page->show();
            return $arr = array('data'=>$data,'page'=>$page);
        }else{
            //搜索
            $where = session('search_oe');

            if($where['chinese_legal_entity_name'] == ''){
                unset($where['chinese_legal_entity_name']);
            }
            if($where['status'] ==''){
                unset($where['status']);
            }
            $seo_ads = getSeoAdAccounts();
            $where['account_id']=['in',$seo_ads];
            $d= D('f_advertiser');
            $count =$d->where($where)->order('id desc')->count();
            $page = new Page($count,$qty);

            $data = $d
                ->where($where)
                ->order('id desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->select();

            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数
            $page = $page->show();
            return $arr = array('data'=>$data,'page'=>$page);
        }



    }

    /**
     * 获取账户余额列表
     * @param int $qty
     * @param string $where
     * @return array
     */
    function getMoneyLimit($qty = 10,$where=''){
        $d= D('account_money_warning');
        $page = new Page($d->where($where)->count(),$qty);

        $data = $d->where($where)->order('add_time desc')->limit($page->firstRow.','.$page->listRows)->select();

        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数

        $page = $page->show();

        return $arr = array('data'=>$data,'page'=>$page);

    }

    /**
     * 获取违规广告账户
     * @param int $qty
     * @param string $where
     * @return array
     */
    function getAccountsCreditLimit($qty = 10){
        if(is_admin_login()){
            $where=[];
            $d= D('fb_account_credit');
            $page = new Page($d->where($where)->count(),$qty);

            $data = $d->alias("c_a")
                ->join("left join __FACEBOOK_ACCOUNT_STATUS__ as s on c_a.account_id = s.account_id")
                ->where($where)
                ->order("s.status asc ,s.account_name desc")
                ->field("c_a.*,s.account_name,s.status")
                ->select();

            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数

            $page = $page->show();

            return $arr = array('data'=>$data,'page'=>$page);
        }else{
            $d= D('fb_account_credit');
            $seo_ads = getSeoAdAccounts();

            $w_num['account_id'] = ['in',$seo_ads];
            $page = new Page($d->where($w_num)->count(),$qty);
            if($seo_ads){
                $whe['c_a.account_id'] = ['in',$seo_ads];
            }else{
                $whe='';
            }

            $data = $d->alias("c_a")
                ->join("left join __FACEBOOK_ACCOUNT_STATUS__ as s on c_a.account_id = s.account_id")
                ->where($whe)
                ->order("s.status asc ,s.account_name desc")
                ->field("c_a.*,s.account_name,s.status")
                ->select();

            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数

            $page = $page->show();

            return $arr = array('data'=>$data,'page'=>$page);
        }


    }

    /**
     * 获取违规事件列表
     * @param int $qty
     * @param string $where
     * @return array
     */
    function getIllegalRemind($qty = 15){
        if(is_admin_login()){
            $type =cookie('url_list_warning')? cookie('url_list_warning'):'all_data';
            $where=$_where=[];
            if($type =='wait_data'){
                $where['is_record']='0';
                $_where['i.is_record']='0';
            }elseif ($type =='today_data'){
                $time=getTodayTime();
                $where['addtime'] = ['between',[$time['begin'],$time['end']]];
                $_where['i.addtime'] = ['between',[$time['begin'],$time['end']]];
            }elseif($type =='all_data'){

            }
            $d= D('f_illegal_promotion');
            $count=$d->where($where)->count();
            $page = new Page($count,$qty);
            $data = $d
                ->alias('i')
                ->join("left join __FACEBOOK_ACCOUNT_STATUS__ as s on i.account_id = s.account_id ")
                ->where($_where)
                ->order('i.addtime desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->field("i.*,i.id as i_id, i.addtime as i_addtime ,s.status as account_status")
                ->select();
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数
            $page = $page->show();
            return $arr = array('data'=>$data,'page'=>$page);
        }else{
            $seo_ads = getSeoAdAccounts();
            $where=$_where=[];
            $where['account_id'] =['in',$seo_ads];
            $_where['i.account_id'] =['in',$seo_ads];
            $type =cookie('url_list_warning')? cookie('url_list_warning'):'all_data';
            if($type =='wait_data'){
                $where['is_record']='0';
                $_where['i.is_record']='0';
            }elseif ($type =='today_data'){
                $time=getTodayTime();
                $where['addtime'] = ['between',[$time['begin'],$time['end']]];
                $_where['i.addtime'] = ['between',[$time['begin'],$time['end']]];
            }elseif($type =='all_data'){

            }
            $d= D('f_illegal_promotion');
            $count=$d->where($where)->count();
            $page = new Page($count,$qty);
            $data = $d
                ->alias('i')
                ->join("left join __FACEBOOK_ACCOUNT_STATUS__ as s on i.account_id = s.account_id ")
                ->where($_where)
                ->order('i.addtime desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->field("i.*,i.id as i_id, i.addtime as i_addtime ,s.status as account_status")
                ->select();
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数
            $page = $page->show();
            return $arr = array('data'=>$data,'page'=>$page);
        }


    }



    /**
     * 获取广告账户可推广url
     * @param int $qty
     * @param string $where
     * @return array
     */
    function getAccountSetUrl($qty = 15,$where=''){

        $d= D('account_fb');
        $page = new Page($d->where($where)->count(),$qty);
        $where = $where=='' ? '' : "a.".$where;
        $data = $d->alias('a')
            ->join("left join __ACCOUNT_PROMOTED_LINKS__ as a_l on a.account_id = a_l.account_id  ")
            ->where($where)
            ->order('a.id desc')
            ->limit($page->firstRow.','.$page->listRows)
            ->field('a.*,a_l.url')
            ->select();

        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数

        $page = $page->show();

        return $arr = array('data'=>$data,'page'=>$page);
    }

    /**
     * 获取已保存url的账户
     * @param int $qty
     * @param string $where
     * @return array
     */
    function getAccountUrlList($qty=15){
        if(is_admin_login()){
            $where=[];
            $serch =cookie('url_link_account_id')? cookie('url_link_account_id') :-1;
            $d= D('account_promoted_links');
            if($serch==-1){
                $where=[];
                $_where=[];
            }else{
                $where['account_id'] = $serch;
                $_where['a.account_id']=$serch;
            }
            $count =$d->where($where)->count();
            $page = new Page($count,$qty);
            $data = $d->alias('a')
                ->join('left join __FACEBOOK_ACCOUNT_STATUS__ as f on a.account_id = f.account_id')
                ->where($_where)
                ->order('a.updatatime desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->field("a.*,f.account_name,f.status")
                ->select();
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数
            $page = $page->show();
            return $arr = array('data'=>$data,'page'=>$page);
        }else{
            $where=[];
            $serch =cookie('url_link_account_id')? cookie('url_link_account_id') :-1;
            $d= D('account_promoted_links');
            if($serch==-1){
                $where=[];
                $_where=[];
            }else{
                $where['account_id'] = $serch;
                $_where['a.account_id']=$serch;
            }
            $seo_ads = getSeoAdAccounts();
            $where['account_id'] = ['in',$seo_ads];
            $_where['account_id'] = ['in',$seo_ads];
            $count =$d->where($where)->count();
            $page = new Page($count,$qty);
            $data = $d->alias('a')
                ->join('left join __FACEBOOK_ACCOUNT_STATUS__ as f on a.account_id = f.account_id')
                ->where($_where)
                ->order('a.updatatime desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->field("a.*,f.account_name,f.status")
                ->select();
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数
            $page = $page->show();
            return $arr = array('data'=>$data,'page'=>$page);
        }

    }

    /**
     * 获取每日新增广告的账户
     * @param int $qty
     * @param int $where
     * @param int $is_account_id
     * @return array
     */
    function getAccountByNewAdLimit($qty=15){
        if(is_admin_login()){
            //等待审核
            $search =cookie('view_img_account_id') ? cookie('view_img_account_id') :-1;
            if($search!=-1){
                $where['account_id']  =$search;
            }
            $type =cookie('view_ad_data');
            if(!$type){
                $type='all_data';
            }
            if($type == 'wait_data'){
                $where['is_check_img']  ='0';
                $where['is_new_ad']  ='1';
            }else{
                //所有数据
                $where['is_new_ad']  ='1';
            }

            //筛选优化师
            $seo_name=cookie('s_seo_name') ? cookie('s_seo_name') :'';
            if($seo_name && $seo_name!='-1'){
                $d_seo = getSeoAdAccounts($seo_name);
                if($d_seo){
                    $where['account_id'] = ['in',$d_seo];
                }else{
                    $msg= new Message();
                    $msg->setMessage('s_seo_msg','当前优化师没有分配数据');
                    $seo_msg='当前优化师没有分配数据';
                }
            }

            $d= D('f_ads');
            $count = $d->group('account_id') ->where($where)->select();
            $count = count($count);

            $page = new Page($count,$qty);
            $array = $d
                ->group('account_id')
                ->where($where)
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数
            $page = $page->show();
            return $arr = array('data'=>$array,'page'=>$page,'seo_msg'=>$seo_msg? $seo_name :'','msg'=> isset($msg)?$msg:'','count'=>$count);
        }else{
            $search =cookie('view_img_account_id') ? cookie('view_img_account_id') :-1;
            $seo_ads= getSeoAdAccounts();
            if ($seo_ads == false){
                echo "<h1>当前登录优化师分配的广告账户，请分配后重新此打开页面！</h1>";die;
            }
            if($search!=-1){
                $where['account_id']  =$search;
            }else{
                $type =cookie('view_ad_data')?cookie('view_ad_data'):'all_data';
                if($type == 'wait_data'){
                    $where['is_check_img']  ='0';
                    $where['is_new_ad']  ='1';
                }else{
                    //所有数据
                    $where['is_new_ad']  ='1';
                }
                $where['account_id'] = ['in',$seo_ads];
            }
            $d= D('f_ads');
            $count = $d->where($where)->group('account_id desc')->select();
            $count= count($count);
            $page = new Page($count,$qty);
            $array = $d->where($where)
                ->group('account_id desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' ); //显示总条数
            $page = $page->show();
            return $arr = array('data'=>$array,'page'=>$page,'count'=>$count);
        }

    }


    /**
     * 获取账户余额和使用情况
     * @param $acc_id
     * @return array
     */
    function getAccountSum($acc_id){
        $account = new AdAccount($acc_id);
        $spend =$account->read(array(
            AdAccountFields::AMOUNT_SPENT,        //获取已花费金额
            AdAccountFields::SPEND_CAP   ,       //账户总额度
        ));
        $res = $spend->getData();
        $data =array(
            'amount_spent' =>$res['amount_spent'],
            'spend_cap' =>$res['spend_cap'],
        );
        return $data;
    }

    /**
     * 通过广告系列id获取点击量和购买数量（转化率）
     * @param $campaign_id
     * @return array
     */
    function getPercent($campaign_id,$date ='yesterday'){
        $campaign = new Campaign($campaign_id);
        $params = array(
            'date_preset' => $date,
        );
        $res = $campaign->getInsights(array(
          AdsInsightsFields::CAMPAIGN_NAME,
          AdsInsightsFields::ACTION_VALUES,     //可获取到购买金额
          AdsInsightsFields::ACTIONS,           //购买个数
          AdsInsightsFields::INLINE_LINK_CLICKS, //点击量
          AdsInsightsFields::ACCOUNT_NAME, //点击量
          AdsInsightsFields::SPEND,
          AdsInsightsFields::CREATED_TIME
        ),$params);
        $data = $res->getResponse()->getContent();
        $arr =array();
        $arr['account_name'] = $data['data'][0]['account_name'];
        if($arr['account_name'] == null){
            return false;
        }
        $arr['date_start'] = $data['data'][0]['date_start'];
        $arr['date_stop'] = $data['data'][0]['date_stop'];
        $arr['created_time'] = $data['data'][0]['created_time'];
        if(strtotime($arr['created_time'])>strtotime($arr['date_start'])){
            //新创建广告
            return false;
        }


        $arr['campaign_id'] = $campaign_id;
        $arr['campaign_name'] = $data['data'][0]['campaign_name'];
        $arr['link_clicks'] = intval($data['data'][0]['inline_link_clicks']);
        $buyPrice  = $data['data'][0]['action_values'];
        if($buyPrice){
            foreach ($buyPrice as $price){
                if($price['action_type'] =='offsite_conversion.fb_pixel_purchase' ){
                    $buy_price = floatval($price['value']);
                    break;
                }
            }
        }
        $buy_price = $buy_price ? $buy_price : 0;
        $arr['buy_price'] = number_format($buy_price,2);
        $arr['spend_price'] = number_format(floatval($data['data'][0]['spend']),2);
        $purchase = $data['data'][0]['actions'];
        if(count($purchase)>0){
            foreach ($purchase as $val){
                if($val['action_type']=='offsite_conversion.fb_pixel_purchase'){
                    //购买数量
                    $arr['buy_num']= $val['value'];
                    break;
                }
            }

        }
        $arr['buy_num'] = $arr['buy_num'] ? intval($arr['buy_num']) : 0;

        return $arr;

    }


    /**
     * 根据账户id可选择返回所有系列id、广告组id、广告id（一次只能返回一个类型）
     * @param bool $acc_id   是否指定账户
     * @param bool $getCampaign     是否返回所有系列id
     * @param bool $getAdset       是否返回所有广告组id
     * @return array
     */
    public function getAllContentId($acc_id=false,$getCampaign=false,$getAdset=false){

        //$acc_id ='act_791641617892869';
        $account = new AdAccount($acc_id);
        $parm = array(
            'limit'=>'1000'
        );
        $ads = $account->getAds(array(
            CampaignFields::ID,
            CampaignFields::NAME,
            CampaignFields::ACCOUNT_ID,
            'campaign_id','campaign','status',
            'adset_id','adset',
            CampaignFields::EFFECTIVE_STATUS,
            CampaignFields::CONFIGURED_STATUS,
        ),$parm);

        $data = array(); //所有开启广告
        foreach ($ads as $k=>$ad) {
            $set = $ad->getData();
            if($set['effective_status'] == 'ACTIVE'){
                $arr=array();
                $arr['account_id']= $set['account_id'];
                $arr['campaign_id']= $set['campaign_id'];
                $arr['campaign_ids']= $set['campaign'];
                $arr['adset_id']= $set['adset_id'];
                $arr['adset_ids']= $set['adset'];
                $arr['status']= $set['status'];
                $arr['configured_status']= $set['configured_status'];
                $arr['effective_status']= $set['effective_status'];
                $arr['ad_id']= $set['id'];
                $arr['ad_name']= $set['name'];
                $data[]=$arr;
            }
        }
        //dump($data);die;
        if($getCampaign ==true){
            //获取所有广告集合id
            $campaign_ids = array();
            if(!empty($data)){
                foreach ($data as $val){
                    $campaign_id = $val['campaign_id'];
                    $campaign_ids[] = $campaign_id;
                }
            }
            $campaign_ids = array_unique($campaign_ids);

            return $campaign_ids;
        }elseif ($getAdset == true){
            //获取所有广告组id
            $adset_ids = array();
            if(!empty($data)){
                foreach ($data as $val){
                    $adset_id = $val['adset_id'];
                    $adset_ids[] = $adset_id;
                }
            }
            $adset_ids = array_unique($adset_ids);
            return $adset_ids;

        }else{
            //所有广告
            return $data;
        }


    }


    /**
     * 根据用户id获取所有投放中广告 方法1
     * @param $account_id
     * @return array
     */
    public function getAdsByAccountId($account_id){
        $account = new AdAccount($account_id);
        $fields = array(
            AdsInsightsFields::ACCOUNT_ID,
            AdsInsightsFields::ACCOUNT_NAME,
            AdsInsightsFields::CAMPAIGN_NAME,
            AdsInsightsFields::CAMPAIGN_ID,
            AdsInsightsFields::ADSET_ID,
            AdsInsightsFields::ADSET_NAME,
            AdsInsightsFields::AD_ID,
            AdsInsightsFields::AD_NAME,
            AdsInsightsFields::UPDATED_TIME,
        );
        $params = [
            'limit' => 3000,
            'date_preset' => InsightsResultDatePresetValues::LIFETIME,
            'level' => 'ad',
            'filtering' => [
                [
                    'field' => 'ad.delivery_info', 'operator' => 'IN', 'value' => ['active']
                ]
            ]
        ];
        $insights = $account->getInsights($fields, $params);
        $ads=array();
        foreach ($insights as $ad){
            $arr = array();
            $arr['account_id'] = $ad->account_id;
            $arr['account_name'] = $ad->account_name;
            $arr['ad_id'] = $ad->ad_id;
            $arr['ad_name'] = $ad->ad_name;
            $arr['adset_id'] = $ad->adset_id;
            $arr['adset_name'] = $ad->adset_name;
            $arr['campaign_id'] = $ad->campaign_id;
            $arr['campaign_name'] = $ad->campaign_name;
            $update_time =$ad->updated_time;
            $update_time = strtotime($update_time);
            $arr['ad_update_time'] = $update_time;
            $ads[] = $arr;
        }
        return $ads;
    }

    /**
     * 根据用户id获取所有投放中广告 只获取广告账户和广告ID
     * @param $account_id
     * @return array
     */
    public function getAdsByAccountIdFew($account_id){
        $account = new AdAccount($account_id);
        $fields = array(
            AdsInsightsFields::ACCOUNT_ID,
            AdsInsightsFields::AD_ID,
        );
        $params = [
            'limit' => 3000,
            'date_preset' => InsightsResultDatePresetValues::LIFETIME,
            'level' => 'ad',
            'filtering' => [
                [
                    'field' => 'ad.delivery_info', 'operator' => 'IN', 'value' => ['active']
                ]
            ]
        ];
        $insights = $account->getInsights($fields, $params);
        $ads=array();
        foreach ($insights as $ad){
            $arr = array();
            $arr['account_id'] = $ad->account_id;
            $arr['ad_id'] = $ad->ad_id;
            $ads[] = $arr;
        }
        return $ads;
    }

    /**
     * 根据用户id获取所有投放中广告 方法2 （获取数据不全面 ）
     * @param $account_id
     * @return array
     */
    public function getAdsByAccountId_two($account_id){
        $account = new AdAccount($account_id);
        $fields = array(
            AdsInsightsFields::ACCOUNT_ID,
            AdsInsightsFields::ACCOUNT_NAME,
            AdsInsightsFields::CAMPAIGN_NAME,
            AdsInsightsFields::CAMPAIGN_ID,
            AdsInsightsFields::ADSET_ID,
            AdsInsightsFields::ADSET_NAME,
            AdsInsightsFields::AD_ID,
            AdsInsightsFields::AD_NAME,
            AdsInsightsFields::UPDATED_TIME,
        );

        $ads_data = $account->getAds($fields, array(
                AdFields::EFFECTIVE_STATUS => array(
                    ArchivableCrudObjectEffectiveStatuses::ACTIVE,
                ),
                'limit' => 3000,
                'date_preset' => InsightsResultDatePresetValues::TODAY,
            )
        );
        dump($ads_data);
        echo 1111111111;
        $ads=array();
        foreach ($ads_data as $ad){
            dump($ad);die;
            $arr = array();
            $arr['account_id'] = $ad->account_id;
            $arr['account_name'] = $ad->account_name;
            $arr['ad_id'] = $ad->ad_id;
            $arr['ad_name'] = $ad->ad_name;
            $arr['adset_id'] = $ad->adset_id;
            $arr['adset_name'] = $ad->adset_name;
            $arr['campaign_id'] = $ad->campaign_id;
            $arr['campaign_name'] = $ad->campaign_name;
            $ads[] = $arr;
        }
        dump($ads);die;
        return $ads;

    }

    /**
     * 获取当日所有广告系列id by database
     * @param $account_id
     * @return array|bool
     */
    public function getCampaignIdByDatabase(){
        $today_ad = D("facebook_ads_temporary");
        $data = $today_ad->alias('t')
            ->join("left join __FACEBOOK_ADS_OLD__ as o on t.facebook_ads_id = o.id")
            ->where("t.is_check_percent = '0'")
            ->group("campaign_id desc")
            ->select();
        shuffle($data);
        if($data){
            return $data;
        }else{
            return false;
        }

    }

    /**
     * 根据用户id （不带act_）获取所有启用广告组id
     * @param $account_id
     * @return array|bool
     */
    public function getAdSetIdByDatabase($account_id){
        $m = D('facebook_ads_old');
        $ads = $m->where(array('account_id'=>"$account_id"))->field('adset_id')->select();
        if($ads){
            $adset_ids = array();
            foreach ($ads as $ad ){
                $adset_ids[] = $ad['adset_id'];
            }

            $adset_ids = array_unique($adset_ids);

            return $adset_ids;
        }else{
            return false;
        }

    }



    /**
     * 根据用户id （不带act_）获取所有启用广告id
     * @param $account_id
     * @return array|bool
     */
    public function getAdIdsByDatabase($account_id){
        $m = D('facebook_ads_old');
        $ads = $m->where(array('account_id'=>"$account_id"))->field('ad_id')->select();
        if($ads){
            $ad_ids = array();
            foreach ($ads as $ad ){
                $ad_ids[] = $ad['ad_id'];
            }
            $ad_ids = array_unique($ad_ids);
            return $ad_ids;
        }else{
            return false;
        }

    }


    /**
     * 通过GET方式获取账户账单
     * @param string $business_id
     * @param int $limit
     * @return bool|mixed
     */
    public function getAccountInvoiceByBusinessIdByGet($start_date ,$end_date,$limit=20,$business_id =''){
        $business_id = $business_id == ''  ? $this->business_id : $business_id;
        $get = new FaceBookGet();
        $url = $business_id."/business_invoices?fields=billed_amount_details,billing_period,entity,id,invoice_id,invoice_type,payment_term,type,ad_account_ids,amount_due,amount,currency,download_uri,due_date,invoice_date,liability_type,payment_status&start_date=".$start_date."&end_date=".$end_date."&limit=".$limit;

        $res = $get->FbGet($url);
        return $res;

    }


    /**
     * 暂停某个广告
     * @param $ad_id
     * @return Ad
     */
    public function updateAdStatus($ad_id ){
        $ad = new Ad($ad_id);
        $res = $ad->update(array(
            Ad::STATUS_PARAM_NAME => Ad::STATUS_PAUSED,
        ));
        return $res;
    }

    /**
     * 删除广告
     * @param $ad_id
     * @return array|\FacebookAds\ApiRequest|\FacebookAds\Cursor|\FacebookAds\Http\ResponseInterface|void|null
     */
    public function delAd($ad_id)
    {
        $ad = new Ad($ad_id);
        $res =$ad->deleteSelf();
        $res = $res->getContent();
        $res = $res['success'];
        return $res;
    }


    /**
     * 根据账户ID和图片hash 返回图片路径
     * @param string $acc_id
     * @param array $hash
     * @return mixed
     */
    public function getImgUrlByHash($acc_id,array $hash)
    {
        $account = new AdAccount("act_".$acc_id);
        $fields = array(
            AdImageFields::URL,
            AdImageFields::HASH,
            AdImageFields::ACCOUNT_ID,
            AdImageFields::PERMALINK_URL,
        );
        $params = [
            'hashes' =>$hash
        ];
        $res = $account->getAdImages($fields,$params)->getResponse()->getContent();
        return $res['data'];
    }

    /**
     * 获取OE数据
     * @return mixed
     */
    public function getOeData(){
        $fbget = new FaceBookGet();
        $url=$this->business_id.'/resellervettingrequests?limit=500';
        $res = $fbget->FbGet($url);
        return $res;
    }


    /**
     * 通过视频ID获取视频截帧图片
     * @param $v_id
     * @return bool|mixed
     */
    public function getVideoImgsByVid($v_id){
        $fb = new FaceBookGet();
        $url = "https://graph.facebook.com/v3.3/".$v_id."?fields=source%2Cthumbnails&access_token=".$fb->token;
        $res = $fb->_get($url);
        return $res ? $res : false;
    }

    /**
     * 获取广告账户拥有的用户
     * @param $acc_id
     * @return mixed
     */
    public function getUsersByAccountId($acc_id)
    {
        $acc_id ='act_'.$acc_id;
        $res = new AdAccount($acc_id);
        $res = $res->getUsers([],[]);
        $res =$res->getResponse()->getContent();
        return $res['data'];
    }

    /**
     * 获取状态
     * @param $num
     * @param $level
     * @return bool
     */
    public function getStatus($num,$level){
        if($level =='campaign'){
            $campaign = new Campaign($num);
            $res = $campaign->read([
               CampaignFields::STATUS
            ]);
            if($res =$res->getData()){
                return $res['status'];
            }else{
                return false;
            }
        }elseif($level == 'adset'){
            $adset = new AdSet($num);
            $res = $adset->read([AdSetFields::STATUS]);
            if($res =$res->getData()){
                return $res['status'];
            }else{
                return false;
            }
        }elseif($level =='ad'){
            $ad  = new Ad($num);
            $res = $ad ->read([AdFields::STATUS]);
            if($res =$res->getData()){
                return $res['status'];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


//https://docs.google.com/spreadsheets/d/1NDbZTRqYIhqSh30y7a3T7t9YmgrZJBFlKitGhx3V6cs/edit?usp=sharing
    public function getAccountsByUserId($user_id=''){

        $res = new User($user_id);
        $res = $res->getAdAccounts(
            $fields=[ 'name','id','account_id'],
            $params =[
            'limit'=> '100',
        ]);
        dump($res);
    }


    public function test(){
        //$fb_app = new FacebookApp('826283441046676','e1dfb09ef3002533d61559d50aa4aae9');
//insert into f_ads_status (account_id,account_name,campaign_id,campaign_name,adset_id,adset_name,ad_id,ad_name)
//　 select account_id,account_name,campaign_id,campaign_name,adset_id,adset_name,ad_id,ad_name from f_ads
        $fb = new FaceBookGet();
        $res = $fb->EtagGet('23843116314440025?fields=status','"f1155f0637d405dcba59cd8041256e705f8c4e6d"');
        dump($res);die;
        $fields = array(
           AdImageFields::URL,
           AdImageFields::HASH,
        );
        $params = [
            'hashes' =>['bfd2025d6720b727ee815f8438f09dc3','b0463e686ccb231089d899dde37a7b44']
        ];
        $adcontent = $ad->getAdImages($fields,$params);
        $adcontent = $adcontent->getResponse()->getContent();
        dump($adcontent);die;
        $adaccount = new AdAccount('act_2296276880587378');
        $ads = $adaccount->getAds();
        dump($ads);

    }

    public function getAdsByAccount()
    {
        $url = "act_286954808742487/insights?level=ad";
        $get = new FaceBookGet();
        $res = $get->FbGet($url);
        dump(count($res));
    }

    //获取总数量?summary=total_count
    //	EAALvgBr8xJQBAJVB298qZBrDhheX8gd9Rfir7Qm8fdOmqFNFV4MEIVEH3yVNhjntZAL2UntMc7zQ8dZBeq2PfzLTbQ8vZAGg8hSAKx4MLzEH8ZA0hMjZAvGyRjHYGyojFBjxGl0lFQlOSeP3PCqmSMT5n3wGvMseIC80ISlVdZBVjkDn0p2Fiaw


}