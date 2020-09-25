<<<<<<< HEAD
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/* load the MX_Router class */
//require APPPATH . "third_party/MX/Controller.php";

class Root_Controller extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

	}
=======
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class Root_Controller extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

    }
>>>>>>> maraj
    public function json_return($array)
    {
        header('Content-type: application/json');
        echo json_encode($array);
        exit();
    }
}
