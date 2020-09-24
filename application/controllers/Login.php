<?php
/**
 * Created by PhpStorm.
 * User: Md Maraj Hossain
 * Date: 9/15/20
 * Time: 1:55 PM
 */

class Login extends Root_controller {
    public function __construct(){
        parent::__construct();
        $this->message="";
    }
    function index(){

        $post = $this->input->post();
        $ajax['post'] = $post;
        $ajax['error']['error_type'] = "";
        $time=time();
        $user=Query_helper::get_info(TABLE_LOGIN_SETUP_USER,'*',array('user_name ="'.$post['user_name'].'"', 'status ="'.SYSTEM_STATUS_ACTIVE.'"'),1);
        if($user){
            if($user['password'] == md5($post['password'])){
                if($user['password_wrong_consecutive']>0){
                    $data=array();
                    $data['password_wrong_consecutive']=0;
                    Query_helper::update(TABLE_LOGIN_SETUP_USER,$data,array("id = ".$user['id']),false);
                }
                $mobile_verification_required = $this->userMobileVerification($user, $post['device']);
                if($mobile_verification_required){
                    /* send opt & return blank user info */
                    $user_info=Query_helper::get_info(TABLE_LOGIN_SETUP_USER_INFO,'*',array('user_id ='.$user['id'] ,'revision =1'),1);
                    if($user_info && (strlen($user_info['mobile_no'])>0)){
                        //send verification code
                        $verification_code=mt_rand(1000,999999);
                        $data=array();
                        $data['user_id']=$user['id'];
                        $data['code_verification']=$verification_code;
                        $data['date_created']=$time;
                        $verification_id=Query_helper::add(TABLE_SYSTEM_HISTORY_LOGIN_VERIFICATION_CODE,$data,false);

                        $string_to_encrypt=$verification_id;
                        /*$encrypted_decrypted_code = Api_maraj::$encrypted_decrypted_code;
                        $password=Api_maraj::$encrypted_decrypted_key;
                        $token_sms_generated=openssl_encrypt($string_to_encrypt,$encrypted_decrypted_code,$password);*/
                        $token_sms_generated=Encrypt_decrypt_helper::get_encrypt($string_to_encrypt);

                        $this->load->helper('mobile_sms');
                        $this->lang->load('mobile_sms');
                        $mobile_no=$user_info['mobile_no'];
                        // Mobile_sms_helper::send_sms(Mobile_sms_helper::$API_SENDER_ID_MALIK_SEEDS,$mobile_no,sprintf($this->lang->line('SMS_LOGIN_OTP'),$verification_code),'text');
                        // $this->session->set_userdata("login_mobile_verification_id", $verification_id);
                        $ajax = array('error_type'=>'OTP_VERIFICATION_REQUIRED','token_sms'=>$token_sms_generated,'otp'=>$verification_code);
                        $ajax['user'] = array();
                        $this->json_return($ajax);
                    } else {
                        //mobile number not set
                        $ajax = array('error_type'=>'MOBILE_NUMBER_NOT_FOUND');
                        $this->json_return($ajax);
                    }
                } else {
                    /* login  */
                    $data = $this->doLogin($post['device'], $user['id']);
                    $ajax['user'] = $data;
                    $ajax['task'] = [];
                    $this->json_return($ajax);
                }
            } else {
                /* wrong password counting query */
                $result=Query_helper::get_info(TABLE_LOGIN_SETUP_SYSTEM_CONFIGURES,array('config_value'),array('purpose ="' .SYSTEM_PURPOSE_LOGIN_MAX_WRONG_PASSWORD.'"','status ="'.SYSTEM_STATUS_ACTIVE.'"'),1);
                $data=array();
                $data['password_wrong_consecutive']=$user['password_wrong_consecutive']+1;
                $data['password_wrong_total']=$user['password_wrong_total']+1;
                $password_remaining=($result['config_value']+1)-$data['password_wrong_consecutive'];

                if($data['password_wrong_consecutive']<=$result['config_value'])//3ed digit 0
                {
                    Query_helper::update(TABLE_LOGIN_SETUP_USER,$data,array("id = ".$user['id']),false);
                    $ajax = array('error_type'=>'PASSWORD_INCORRECT','remaining'=>$password_remaining);
                    $this->json_return($ajax);
                }
                else//3rd digit 1
                {
                    $data['status']=SYSTEM_STATUS_INACTIVE;
                    $data['remarks_status_change']=sprintf($this->lang->line('REMARKS_USER_SUSPEND_WRONG_PASSWORD'),$data['password_wrong_consecutive']);
                    $data['date_status_changed'] = $time;
                    $data['user_status_changed'] = -1;
                    Query_helper::update(TABLE_LOGIN_SETUP_USER,$data,array("id = ".$user['id']),false);
                    $ajax = array('error_type'=>'PASSWORD_RETRY_EXCEEDED');
                    $this->json_return($ajax);
                }
            }
        } else {
            $ajax = array('error_type'=>'USER_NOT_FOUND');
            $this->json_return($ajax);
        }
    }
    public function loginSMS(){
        $time = time();
        $post = $this->input->post();
        /*$post['device']=array('token_device'=>$post['tokenDevice']); // optional just check
        $ajax['post'] = $post;*/
        /*$user_name = $post['user_name'];
        $user_password = $post['password'];*/
        $otp = $post['otp'];
        $verification_id = Encrypt_decrypt_helper::get_encrypt($post['token_sms']);
        if(isset($post['otp']) && $post['otp']){
            $item=Query_helper::get_info(TABLE_SYSTEM_HISTORY_LOGIN_VERIFICATION_CODE,'*',array('id ="'.$verification_id.'"','code_verification ="'.$otp.'"'),1);
            if($item){
                if(($item['status_used'])==SYSTEM_STATUS_YES){
                    $ajax = array('error_type'=>'OTP_ALREADY_USED');
                    $this->json_return($ajax);
                } else {
                    if(($time-$item['date_created'])>User_helper::$mobile_verification_code_expires){
                        $ajax = array('error_type'=>'OTP_EXPIRED');
                        $this->json_return($ajax);
                    } else {
                        //$user=Query_helper::get_info(TABLE_LOGIN_SETUP_USER,'*',array('user_name ="'.$user_name.'"', 'status ="'.$this->config->item('system_status_active').'"'),1);
                        $results = Query_helper::get_info(TABLE_SYSTEM_HISTORY_LOGIN_VERIFICATION_CODE,'*',array('user_id ="'.$item['user_id'].'"'),5,array('id'=>'DESC'));
                        $number_of_verification_code=0;
                        foreach($results as $result){
                            if($result['status_used'] == SYSTEM_STATUS_YES){
                                $number_of_verification_code += 1;
                            }
                        }
                        //if(sizeof($number_of_verification_code)>Api_maraj::$number_of_verification_code_try){
                        //if(sizeof($number_of_verification_code)>5){
                        if($number_of_verification_code>5){
                            $data=array(
                                'status' => SYSTEM_STATUS_YES
                            );
                            Query_helper::update(TABLE_LOGIN_SETUP_USER,$data,array("id = ".$item['user_id']),false);
                            $ajax = array('status_code'=>'302');
                            $this->json_return($ajax);
                        }
                        $data=array();
                        $data['status_used']=SYSTEM_STATUS_YES;
                        $data['date_updated']=$time;
                        Query_helper::update(TABLE_SYSTEM_HISTORY_LOGIN_VERIFICATION_CODE,$data,array("id = ".$item['id']),false);

                        $data = $this->doLogin($post['device'], $item['user_id']);
                        $ajax['user'] = $data;
                        $ajax['task'] = [];
                        $this->json_return($ajax);
                    }
                }
            } else {
                $ajax = array('error_type'=>'OTP_INCORRECT');
                $this->json_return($ajax);
            }
        } else {
            $ajax = array('error_type'=>'OTP_NOT_SEND');
            $this->json_return($ajax);
        }
    }
    private function userMobileVerification($userInfo, $device){
        $time = time();
        $mobile_verification_required=true;
        if($userInfo['time_mobile_authentication_off_end']>$time){ // own mobile verification setting check
            $mobile_verification_required=false;
        } else {
            $result=Query_helper::get_info(TABLE_LOGIN_SETUP_SYSTEM_CONFIGURES,array('config_value'),array('purpose ="' .SYSTEM_PURPOSE_LOGIN_STATUS_MOBILE_VERIFICATION.'"','status ="'.SYSTEM_STATUS_ACTIVE.'"'),1);
            if($result && ($result['config_value']!=1)){  // global mobile verification setting check
                $mobile_verification_required=false;
            } else {
                // $mobile_verification_required=true;
                $item_device=Query_helper::get_info(TABLE_LOGIN_USER_DEVICES,array('*'),array('token_device ="' .$device['token_device'].'"'),1);
                if($item_device){ // check number of device allow for me.
                    $max_logged_browser=1;
                    if($userInfo['max_logged_browser']>0)
                    {
                        $max_logged_browser=$userInfo['max_logged_browser'];
                    }
                    $this->db->from(TABLE_LOGIN_USER_SESSIONS.' us');
                    $this->db->select('us.id, us.device_id, us.user_id, us.token_auth, ud.token_device');
                    $this->db->join(TABLE_LOGIN_USER_DEVICES.' ud','ud.id = us.device_id');
                    $this->db->where('us.user_id',$userInfo['id']);
                    $this->db->order_by('us.time_expire DESC');
                    $this->db->limit($max_logged_browser);
                    $results=$this->db->get()->result_array();
                    foreach($results as $result){
                        if($result['device_id'] == $item_device['id']){
                            $mobile_verification_required=false;
                            break;
                        }
                    }
                }
            }
        }
        return $mobile_verification_required;
    }
    private function doLogin($device, $user_id){
        $time=time();
        $string_to_encrypt=$time.'_'.$user_id;
        $token_auth_generated = Encrypt_decrypt_helper::get_encrypt($string_to_encrypt);

        if(isset($device['token_device']) && $device['token_device']){
            $result=Query_helper::get_info(TABLE_LOGIN_USER_DEVICES,array('*'),array('token_device ="' .$device['token_device'].'"'),1);
            $token_device = $result['token_device'];
            if($result){
                $device_id = $result['id'];
                // $data_device = $device;
            } else {
                $return['user']=(object) array();
                $return['token_device']='';
                $return['error']='token_device_invalid';
                return $return;
            }
        } else {

            $token_device_generated= Encrypt_decrypt_helper::get_encrypt($time);
            $token_device = $token_device_generated;
            $data_device=array(
                'token_device' => $token_device_generated,
                'device_info' => json_encode(array('device_name'=>'SAMSUNG', 'device_code'=>'A20-2020', 'device_model'=>'A20')),
                'ip'=> '127.0.0.1',
                'time_register' => $time
            );
            $device_id=Query_helper::add(TABLE_LOGIN_USER_DEVICES,$data_device,false);
        }
        $result = Query_helper::get_info(TABLE_LOGIN_USER_SESSIONS,array('*'),array('user_id ="' .$user_id.'"', 'device_id ="' .$device_id.'"'),1);
        if($result){
            $data_session = array(
                'token_auth'=> $token_auth_generated,
                'time_expire'=> $time,
            );
            Query_helper::update(TABLE_LOGIN_USER_SESSIONS,$data_session,array("id = ".$result['id']),false);
        } else {
            $data_session = array(
                'user_id'=> $user_id,
                'device_id'=> $device_id,
                'token_auth'=> $token_auth_generated,
                'time_expire'=> $time,
            );
            Query_helper::add(TABLE_LOGIN_USER_SESSIONS,$data_session,false);
        }

        $this->db->from(TABLE_LOGIN_SETUP_USER.' user');
        $this->db->join(TABLE_LOGIN_SETUP_USER_INFO.' user_info','user_info.user_id = user.id AND user_info.revision = 1');
        $this->db->select('user_info.name user_full_name, user_info.user_group');
        $this->db->where('user.id',$user_id);
        $result = $this->db->get()->row_array();
        if(! $result){
            $return['user']=(object) array();
            $return['token_device']='';
            $return['error']='token_device_invalid';
            return $return;
        }

        $user['tokenAuth']=$token_auth_generated;
        $user['tokenSave']='';
        $user['tokenBrowser']='';
        $user['userId']=$user_id;
        $user['userInfo']=array(
            'user_full_name' => $result['user_full_name'],
            'user_group' => $result['user_group'],
        );
        $deviceToken['token_device']=$token_device;
        $error['error_type']='';
        return array('error'=>$error,'user'=>$user,'device'=>$deviceToken);
    }
} 