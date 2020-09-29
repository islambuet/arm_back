<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload_helper
{
    public static $UPLOAD_API_URL = 'http://45.251.59.5/api_file_server/upload';
    public static $IMAGE_LOGIN_BASE_URL = 'http://45.251.59.5/login_2018_19/';
    public static $SITE_ROOT_FOLDER = 'rnd_2020';
    public static $UPLOAD_IMAGE_AUTH_KEY = 'rnd_2020';
    public static function upload_file($save_dir='images',$allowed_types='gif|jpg|png|jpeg',$max_size=10240)
    {
        $CI= & get_instance();
        $uploaded_files=array();
        if(sizeof($_FILES)>0)
        {
            $file_selected=false;
            $file_size_ok=true;
            foreach ($_FILES as $key=>$value)
            {
                if(strlen($value['name'])>0)
                {
                    $file_selected=true;
                    if ($value['size']>($max_size*1000))
                    {
                        $file_size_ok=false;
                        $uploaded_files[$key]=array('status'=>false,'message'=>$value['name'].': File size is high');
                    }
                }
            }
            //upload to file server
            if($file_selected && $file_size_ok)
            {
                // create curl resource
                $ch = curl_init();
                // set url
                curl_setopt($ch, CURLOPT_URL, Upload_helper::$UPLOAD_API_URL);

                //set to post data
                curl_setopt($ch, CURLOPT_POST,TRUE);
                $data = array();
                $data['upload_site_root_dir']=Upload_helper::$SITE_ROOT_FOLDER;
                $data['upload_auth_key']=Upload_helper::$UPLOAD_IMAGE_AUTH_KEY;
                $data['save_dir']=$save_dir;
                $data['allowed_types']=$allowed_types;
                $data['max_size']=$max_size;
                foreach ($_FILES as $key=>$value)
                {
                    if(strlen($value['name'])>0)
                    {
                        //also check max size here
                        $data[$key] = new CURLFile($value['tmp_name'],$value['type'], $value['name']);
                    }
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

                //return the transfer as a string
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);

                // $output contains the output string
                $response = curl_exec($ch);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if($http_status==200)
                {
                    $response_array=json_decode($response,true);
                    if($response_array['status'])
                    {
                        $uploaded_files=$response_array['uploaded_files'];
                    }
                    else
                    {
                        foreach ($_FILES as $key=>$value)
                        {
                            if(strlen($value['name'])>0)
                            {
                                $uploaded_files[$key]=array('status'=>false,'message'=>$response_array['response_message']);
                            }
                        }
                    }
                }
                else
                {
                    foreach ($_FILES as $key=>$value)
                    {
                        if(strlen($value['name'])>0)
                        {
                            $uploaded_files[$key]=array('status'=>false,'message'=>'Store Server unavailable.-'.$http_status);
                        }
                    }
                }
                // close curl resource to free up system resources
                curl_close($ch);
            }
        }

        return $uploaded_files;
    }
}
