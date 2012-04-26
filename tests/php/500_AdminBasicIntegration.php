<?
require_once(dirname(__FILE__) . '/base.php');
require_once(dirname(__FILE__) . '/curl.php');

class AdminBasicIntegration extends UnitTestCase {
	public $cc;
	private $cash_test_url=false;
	private $cash_user_id=1;
	private $cash_user_login='email@example.com';
	private $cash_user_password='hack_my_gibson';
	
	public function __construct() {
		$this->cc = new cURL();
		$this->cash_test_url = getTestEnv('CASHMUSIC_TEST_URL');
		echo "Testing basic admin integration at:\n" . $this->cash_test_url . "\n";
		// force a static login for CI workers (Travs, etc):
		$force_login = getTestEnv('CASH_CI_LOGIN');
		$force_password = getTestEnv('CASH_CI_PASSWORD');
		if ($force_login) {
			$this->cash_user_login = $force_login;
			$this->cash_user_password = $force_password;
		} else {
			$user_add_request = new CASHRequest(
				array(
					'cash_request_type' => 'system', 
					'cash_action' => 'addlogin',
					'address' => $this->cash_user_login,
					'password' => $this->cash_user_password,
					'is_admin' => 1
				)
			);
			$this->cash_user_id = $user_add_request->response['payload'];
		}
    }
	
	public function testLogin() {
		if ($this->cash_test_url) {
			// make sure we get the login page
			$src = $this->cc->get($this->cash_test_url . '/interfaces/php/admin/');
			$this->assertPattern('/<h1>Log In:<\/h1>/', $src);
		
			// look for an incorrect login
			$src = $this->cc->post(
				$this->cash_test_url . '/interfaces/php/admin/',
				http_build_query(array(
					'address'=>'false@example.com',
					'password'=>'incorrect',
					'login'=>'1'
				))
			);
			$this->assertPattern('/<h1 class="tryagain">Try Again:<\/h1>/', $src);
		
			// now try a good login
			$src = $this->cc->post(
				$this->cash_test_url . '/interfaces/php/admin/',
				http_build_query(array(
					'address'=>$this->cash_user_login,
					'password'=>$this->cash_user_password,
					'login'=>'1'
				))
			);
			$this->assertPattern('/<h1>CASH Music: Main Page<\/h1>/', $src);
		
			// make sure the cookie is persistent
			$src = $this->cc->get($this->cash_test_url . '/interfaces/php/admin/');
			$this->assertPattern('/<h1>CASH Music: Main Page<\/h1>/', $src);
		}
    }
}
?>