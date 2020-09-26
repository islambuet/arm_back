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
        $this->load->helper('encrypt_decrypt');

    }
    function index(){
        // 3 item  post. 1. mobile_no, 2. password, 3. device['token_device']
        $post = $this->input->post();
        /*$ajax['post'] = $post;
        $ajax['error']['error_type'] = "";*/
        $token_device = isset($post['device']['token_device'])?$post['device']['token_device']:'';
        $time=time();
        $user=Query_helper::get_info(TABLE_LOGIN_SETUP_USER,'*',array('mobile_no ="'.$post['mobile_no'].'"', 'status ="'.SYSTEM_STATUS_ACTIVE.'"'),1);
        if($user){
            if($user['password'] == md5($post['password'])){
                if($user['password_wrong_consecutive']>0){
                    $data=array();
                    $data['password_wrong_consecutive']=0;
                    Query_helper::update(TABLE_LOGIN_SETUP_USER,$data,array("id = ".$user['id']),false);
                }
                $mobile_verification_required = $this->user_mobile_verification($user, $token_device);
                if($mobile_verification_required){
                    /* send opt & return blank user info */
                    //send verification code
                    $verification_code=mt_rand(1000,999999);
                    $data=array();
                    $data['user_id']=$user['id'];
                    $data['otp']=$verification_code;
                    $data['date_created']=$time;
                    $verification_id=Query_helper::add(TABLE_SYSTEM_HISTORY_LOGIN_OTP,$data,false);

                    $string_to_encrypt=$verification_id;
                    $token_sms_generated=Encrypt_decrypt_helper::get_encrypt($string_to_encrypt);

                    $this->load->helper('mobile_sms');
                    $this->lang->load('mobile_sms');
                    $mobile_no=$post['mobile_no'];
                    // Mobile_sms_helper::send_sms(Mobile_sms_helper::$API_SENDER_ID_MALIK_SEEDS,$mobile_no,sprintf($this->lang->line('SMS_LOGIN_OTP'),$verification_code),'text');
                    // $this->session->set_userdata("login_mobile_verification_id", $verification_id);
                    $ajax = array('error_type'=>'OTP_VERIFICATION_REQUIRED','token_sms'=>$token_sms_generated,'otp'=>$verification_code);
                    $ajax['user'] = array();
                    $this->json_return($ajax);

                } else {
                    /* login  */
                    $data = $this->do_login($post['device'], $user['id']);
                    $ajax = $data;
                    $this->json_return($ajax);
                }
            } else {
                /* wrong password counting query */
                $get_max_wrong_password = Configuration_helper::get_max_wrong_password();
                $data=array();
                $data['password_wrong_consecutive']=$user['password_wrong_consecutive']+1;
                $data['password_wrong_total']=$user['password_wrong_total']+1;
                $password_remaining=($get_max_wrong_password+1)-$data['password_wrong_consecutive'];
                if($data['password_wrong_consecutive']<=$get_max_wrong_password)//3ed digit 0
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
    public function login_sms(){
        // 3 item  post. 1. otp, 2. token_sms, 3. device['token_device'] = full info
        $time = time();
        $post = $this->input->post();
        $verification_id = Encrypt_decrypt_helper::get_decrypt(isset($post['token_sms'])?$post['token_sms']:'');
        if(isset($post['otp']) && $post['otp']){
            $item=Query_helper::get_info(TABLE_SYSTEM_HISTORY_LOGIN_OTP,'*',array('id ="'.$verification_id.'"','otp ="'.$post['otp'].'"'),1);
            if($item){
                if(($item['status_used'])==SYSTEM_STATUS_YES){
                    $ajax = array('error_type'=>'OTP_ALREADY_USED');
                    $this->json_return($ajax);
                } else {
                    if(($time-$item['date_created'])>Configuration_helper::get_otp_expire()){
                        $ajax = array('error_type'=>'OTP_EXPIRED');
                        $this->json_return($ajax);
                    } else {
                        // discussion 108 - 124 line.
                        $results = Query_helper::get_info(TABLE_SYSTEM_HISTORY_LOGIN_OTP,'*',array('user_id ="'.$item['user_id'].'"'),Configuration_helper::get_otp_limit_last_number(),0,array('id DESC'));
                        $number_of_otp=0;
                        foreach($results as $result){
                            if($result['status_used'] == SYSTEM_STATUS_YES){
                                $number_of_otp += 1;
                            }
                        }
                        if($number_of_otp>Configuration_helper::get_number_of_otp_check()){
                            $data=array(
                                'status' => SYSTEM_STATUS_YES
                            );
                            Query_helper::update(TABLE_LOGIN_SETUP_USER,$data,array("id = ".$item['user_id']),false); // i think this is wrong query
                            $ajax = array('status_code'=>'302');
                            $this->json_return($ajax);
                        }
                        $data=array();
                        $data['status_used']=SYSTEM_STATUS_YES;
                        $data['date_updated']=$time;
                        Query_helper::update(TABLE_SYSTEM_HISTORY_LOGIN_OTP,$data,array("id = ".$item['id']),false);

                        $data = $this->do_login($post['device'], $item['user_id']);
                        $ajax = $data;
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
    private function user_mobile_verification($user, $token_device){
        $time = time();
        $mobile_verification_required=true;
        if($user['time_otp_off_end']>$time){ // own mobile verification setting check
            $mobile_verification_required=false;
        } else {
            $get_mobile_verification_status = Configuration_helper::get_mobile_verification_status();
            if($get_mobile_verification_status!=1){  // global mobile verification setting check
                $mobile_verification_required=false;
            } else {
                // $mobile_verification_required=true;
                $item_device=Query_helper::get_info(TABLE_LOGIN_USER_DEVICES,array('*'),array('token_device ="'.$token_device.'"'),1);
                if($item_device){ // check number of device allow for me.
                    $max_logged_browser=1;
                    if($user['max_logged_browser']>0)
                    {
                        $max_logged_browser=$user['max_logged_browser'];
                    }
                    $this->db->from(TABLE_LOGIN_USER_SESSIONS.' us');
                    $this->db->select('us.id, us.device_id, us.user_id, us.token_auth, ud.token_device');
                    $this->db->join(TABLE_LOGIN_USER_DEVICES.' ud','ud.id = us.device_id');
                    $this->db->where('us.user_id',$user['id']);
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
    private function do_login($device, $user_id){
        $time=time();
        $token_auth_generated = Encrypt_decrypt_helper::get_encrypt('Auth_'.$time.'_'.$user_id);
        $token_csrf_generated = Encrypt_decrypt_helper::get_encrypt('CSRF_'.$time.'_'.$user_id);
        if($device['token_device']){
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
                'device_info' => json_encode($device),
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
                'csrf_new'=> $token_csrf_generated,
                'csrf_old'=> $result['csrf_new'],
            );
            Query_helper::update(TABLE_LOGIN_USER_SESSIONS,$data_session,array("id = ".$result['id']),false);
        } else {
            $data_session = array(
                'user_id'=> $user_id,
                'device_id'=> $device_id,
                'token_auth'=> $token_auth_generated,
                'time_expire'=> $time,
                'csrf_new'=> $token_csrf_generated,
            );
            Query_helper::add(TABLE_LOGIN_USER_SESSIONS,$data_session,false);
        }

        $this->db->from(TABLE_LOGIN_SETUP_USER.' user');
        /*$this->db->join(TABLE_LOGIN_SETUP_USER_INFO.' user_info','user_info.user_id = user.id AND user_info.revision = 1');
        $this->db->select('user_info.name user_full_name, user_info.user_group');*/
        $this->db->where('user.id',$user_id);
        $result = $this->db->get()->row_array();
        if(! $result){
            $return['user']=(object) array();
            $return['token_device']='';
            $return['error_type']='token_device_invalid';
            return $return;
        }


        /*$user['token_auth']=$token_auth_generated;
        $user['token_csrf']='';
        $user['token_device']=$token_device;
        $user['id']=$user_id;
        $user['info']=array(
            'name_en' => $result['name_en'],
            'name_bn' => $result['name_bn'],
            'user_group_id' => $result['user_group_id'],
        );
        //$device['token_device']=$token_device;
        $error['error_type']='';
        return array('error'=>$error,'user'=>$user,'device'=>array('token_device'=>$token_device));*/
        $response = array();
        $response['error_type'] = '';
        $response['user']['token_auth']=$token_auth_generated;
        $response['user']['token_csrf'] = $token_csrf_generated;
        $response['user']['token_device'] = $token_device;
        $response['user']['id'] = $result['id'];
        $response['user']['name_en'] = $result['name_en'];
        $response['user']['name_bn'] = $result['name_bn'];
        $response['user']['info'] = $result;
        $response['user']['tasks'] = Module_task_helper::get_tasks($result['user_group_id']);
        return $response;
    }
} 