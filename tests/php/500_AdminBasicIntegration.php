<?php
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
		if ($this->cash_test_url == 'http://dev.cashmusic.org:8080') {
			echo "Test URL is pointing to an external test server, skipping integration tests.\n";
			$this->cash_test_url = false;
		} else {
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
						'is_admin' => true,
						'force52compatibility' => true
					)
				);
				$this->cash_user_id = $user_add_request->response['payload'];
			}
		}
    }
	
	public function testLogin() {
		if ($this->cash_test_url) {
			// make sure we get the login page
			$src = $this->cc->get($this->cash_test_url . '/interfaces/php/admin/');
			$this->assertPattern('/<h1>Hello. Log In:<\/h1>/', $src);
		
			// look for an incorrect login
			$src = $this->cc->post(
				$this->cash_test_url . '/interfaces/php/admin/',
				http_build_query(array(
					'address'            => 'false@example.com',
					'password'           => 'incorrect',
					'browseridassertion' => -1,
					'login'              => '1'
				))
			);
			$this->assertPattern('/<h1 class="tryagain">Try Again:<\/h1>/', $src);
		
			// now try a good login
			$src = $this->cc->post(
				$this->cash_test_url . '/interfaces/php/admin/',
				http_build_query(array(
					'address'            => $this->cash_user_login,
					'password'           => $this->cash_user_password,
					'browseridassertion' => -1,
					'login'              => '1'
				))
			);
			$this->assertPattern('/<h1 id="pagetitle">CASH Music: Main Page<\/h1>/', $src);

			// make sure the cookie is persistent
			$src = $this->cc->get($this->cash_test_url . '/interfaces/php/admin/');
			$this->assertPattern('/<h1 id="pagetitle">CASH Music: Main Page<\/h1>/', $src);
		}
    }

    public function testAllRoutes() {
    	if ($this->cash_test_url) {
	    	// run through all known routes and make sure we're getting pages, not error messages
	    	$all_routes = json_decode(file_get_contents(dirname(__FILE__) . '/../../interfaces/php/admin/components/menu/menu_en.json'),true);
	    	foreach ($all_routes as $route => $details) {
	    		$src = $this->cc->get($this->cash_test_url . '/' . $route);
	    		$this->assertPattern('/<html/', $src);
	    		$this->assertPattern('/<body/', $src);
	    		$this->assertPattern('/<\/body/', $src);
	    		$this->assertPattern('/<\/html/', $src);
				$this->assertNoPattern('/<h1>Page Not Found<\/h1>/', $src);
	    	}
    	}
    }
}
?>