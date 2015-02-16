<?php
require_once(dirname(__FILE__) . '/base.php');
require_once(dirname(__FILE__) . '/curl.php');

class AdminBasicIntegration extends UnitTestCase {
	public $cc;
	private $cash_test_url=false;
	private $cash_user_id=1;
	private $cash_user_login='dev@cashmusic.org';
	private $cash_user_password='dev';
	
	public function __construct() {
		$this->cc = new cURL();
		$this->cash_test_url = getTestEnv('CASHMUSIC_TEST_URL');
		if (empty($this->cash_test_url)) {
			$this->cash_test_url = 'http://localhost';
		}
		if ($this->cash_test_url == 'http://dev.cashmusic.org') {
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
						'is_admin' => true
					)
				);
				$this->cash_user_id = $user_add_request->response['payload'];
			}
		}
    }
	
	public function testLogin() {
		if ($this->cash_test_url) {
			// make sure we get the login page
			$src = $this->cc->get($this->cash_test_url . '/admin/');
			$this->assertPattern('/<input type="email" name="address" value="" \/>/', $src);
		
			// look for an incorrect login
			$src = $this->cc->post(
				$this->cash_test_url . '/admin/',
				http_build_query(array(
					'address'            => 'false@example.com',
					'password'           => 'incorrect',
					'login'              => '1'
				))
			);
			$this->assertPattern('/<input type="email" name="address" value="" \/>/', $src); // not seeing main page
		
			// now try a good login
			$src = $this->cc->post(
				$this->cash_test_url . '/admin/',
				http_build_query(array(
					'address'            => $this->cash_user_login,
					'password'           => $this->cash_user_password,
					'login'              => '1'
				))
			);
			$this->assertPattern('/<div id="logoutbtn" class="toggle">/', $src);

			// make sure the cookie is persistent
			$src = $this->cc->get($this->cash_test_url . '/admin/');
			$this->assertPattern('/<div id="logoutbtn" class="toggle">/', $src);
		}
    }

    /*
    public function testAllRoutes() {
    	if ($this->cash_test_url) {
	    	// run through all known routes and make sure we're getting pages, not error messages
	    	$all_routes = json_decode(file_get_contents(dirname(__FILE__) . '/../../interfaces/admin/components/interface/en/menu.json'),true);
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
    */
}
?>