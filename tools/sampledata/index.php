<!DOCTYPE html>
<html lang="en">
<head> 
<title>CASH Music | Platform</title>
<meta name="description" content="CASH Music is a nonprofit that empowers musicians through free/open technology and learning." />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width" />

<link rel="icon" href="/docs/assets/images/favicon.ico" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="/docs/assets/css/foundation.min.css" />
<link href='//fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css"  href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" />
<link rel="stylesheet" type="text/css" href="/docs/assets/css/app.css" />

<script type="text/javascript" src="http://js.cashmusic.org/cashmusic.js" data-options="lightboxvideo"></script>

</head> 
<body>

<div id="nav">
	<h1 id="logo"><a href="/"><img src="/docs/assets/images/invert_logo.png" alt="CASH Music"></a></h1>

		<ul>
			<li><a href="/tools/"><i class="icon icon-cog"></i><span>Tools</span></a></li>
			<li><a href="/docs/"><i class="icon icon-book"></i><span>Docs</span></a></li>
			<li><a href="/interfaces/admin/"><i class="icon icon-pencil"></i><span>Login</span></a></li>
			<li><a href="/"><i class="icon icon-certificate"></i><span>Home</span></a></li>
		</ul>
</div>

	<div id="mainspc" class="row">

		<h1>Sample data</h1>

		<h2>Clicking around aimlessly to fill up enough data to test with is boring. Use this to add a bunch of sample data to your platform install.
		</h2>

		<div style="min-height:350px;">
		

		<?php
			if (isset($_POST['noreallygo'])) {

				include_once('/vagrant/framework/cashmusic.php');

				// add a list
				$cr = new CASHRequest(
					array(
						'cash_request_type' => 'people', 
						'cash_action' => 'addlist',
						'name' => 'Sample List',
						'description' => 'This is a description of a test list',
						'user_id' => 1,
					)
				);
				// list's ID will be = $cr->response['payload']
				$list_id = $cr->response['payload'];

				// fill the list
				$people_array = array(
					array('info@cashmusic.org','CASH','Music','get in touch!'),
					array('anonymous@domain.com','','','No name.'),
					array('one@domain.com','Sherlock','Holmes',''),
					array('two@domain.com','John','Watson',''),
					array('three@domain.com','Jane','Marple',''),
					array('four@domain.com','Coach','Taylor','Listen up.'),
					array('five@domain.com','Tammy','Taylor','Hi y\'all'),
					array('six@domain.com','Freddie','Mercury','No comment.'),
					array('seven@domain.com','Little Bo','Dog',''),
					array('eight@domain.com','IP','Freely',''),
					array('nine@domain.com','Koji','Uehara','ボストンレッドソックスの上原浩治です。明るく楽しく野球がしたい。その為にも結果がほしい。ガムシャラに頑張ります。'),
					array('ten@domain.com','David','Ortiz',''),
					array('eleven@domain.com','John','Johnson',''),
					array('twelve@domain.com','Who','Ever','I suppose we should also check out a really really really long comment in English, too.'),
					array('thirteen@domain.com','Stan','Lee',''),
					array('fourteen@domain.com','Otto','Octavius',''),
					array('fifteen@domain.com','Kitty','Pride',''),
					array('sixteen@domain.com','Ziggie','Stardust','OKAYFINE'),
					array('seventeen@domain.com','Joan','Jett','')
				);
				foreach ($people_array as $person) {
					new CASHRequest(
						array(
							'cash_request_type' => 'people', 
							'cash_action' => 'addaddresstolist',
							'address' => $person[0],
							'list_id' => $list_id,
							'first_name' => $person[1],
							'last_name' => $person[2],
							'initial_comment' => $person[3],
							'do_not_verify' => true,
						)
					);
				}

				$cr = new CASHRequest(
					array(
						'cash_request_type' => 'asset', 
						'cash_action' => 'addasset',
						'title' => 'Sample release',
						'description' => 'This is a sample release. I\'m sorry I thought that was sort of obvious.',
						'parent_id' => 0,
						'user_id' => 1,
						'type' => 'release'
					)
				);
				// $cr->response['payload'] will be the release id
				$release_id = $cr->response['payload'];

				$cr = new CASHRequest(
					array(
						'cash_request_type' => 'asset', 
						'cash_action' => 'addasset',
						'title' => 'Sample release cover',
						'description' => 'This is the cover for the sample release. See?',
						'parent_id' => $release_id,
						'location' => 'http://localhost:8888/docs/assets/images/bg_top.jpg',
						'user_id' => 1,
						'type' => 'file'
					)
				);

				// release metadata
				$release_metadata = array(
					'artist_name' => 'The Testers',
					'release_date' => 'January 13, 2014',
					'matrix_number' => 'test001',
					'label_name' => 'Secretly Testnadian',
					'genre' => 'Rock and Roll',
					'copyright' => 'The Testers',
					'publishing' => 'Test Music, BMI',
					'fulfillment' => array(),
					'private' => array(),
					'cover' => $cr->response['payload'],
					'publicist_name' => 'Test Media',
					'publicist_email' => 'test@testmedia.com',
					'onesheet' => 'Test bio'
				);

				// add the cover to the release
				new CASHRequest(
					array(
						'cash_request_type' => 'asset',
						'cash_action' => 'editasset',
						'id' => $release_id,
						'metadata' => $release_metadata
					)
				);

				$cr = new CASHRequest(
					array(
						'cash_request_type' => 'asset', 
						'cash_action' => 'addasset',
						'title' => 'Sample file (dog badges)',
						'description' => 'Dogs are awesome.',
						'parent_id' => 0,
						'location' => 'http://localhost:8888/docs/assets/images/badges.png',
						'user_id' => 1,
						'type' => 'file'
					)
				);
				$file_id = $cr->response['payload'];

				// commerce
				new CASHRequest(
					array(
						'cash_request_type' => 'commerce', 
						'cash_action' => 'additem',
						'user_id' => 1,
						'name' => 'Sample item',
						'description' => 'This is a description for the test item.',
						'sku' => '#abc123',
						'price' => 5.99,
						'available_units' => -1,
						'digital_fulfillment' => 1,
						'physical_fulfillment' => 0,
						'flexible_price' => 1
					)
				);

				$c = new CASHConnection(1);
				$c_id = $c->setSettings(
					'Fake Paypal connection',
					'com.paypal',
					array('whatever' => 0)
				);

				$cr = new CASHRequest(
					array(
						'cash_request_type' => 'commerce', 
						'cash_action' => 'addtransaction',
						'user_id' => 1,
						'connection_id' => $c_id,
						'connection_type' => 'com.paypal',
						'service_timestamp' => time(),
						'service_transaction_id' => '13b2513adb39d',
						'data_sent' => '{"TOKEN":"EC-3CC324611K842411K","CHECKOUTSTATUS":"PaymentActionNotInitiated","TIMESTAMP":"2014-10-02T19:16:19Z","CORRELATIONID":"eaa324c4ce9ge","ACK":"Success","VERSION":"63.0","BUILD":"3719653","EMAIL":"fake@buyer.com","PAYERID":"KW2MPO2RWV4SW","PAYERSTATUS":"verified","FIRSTNAME":"Fake","LASTNAME":"McTest","COUNTRYCODE":"US","CURRENCYCODE":"USD","AMT":"5.99","ITEMAMT":"5.99","SHIPPINGAMT":"0.00","HANDLINGAMT":"0.00","TAXAMT":"0.00","DESC":"Sample item","INSURANCEAMT":"0.00","SHIPDISCAMT":"0.00","L_NAME0":"Sample item","L_NUMBER0":"order-6","L_QTY0":"1","L_TAXAMT0":"0.00","L_AMT0":"5.99","L_ITEMWEIGHTVALUE0":"   0.00000","L_ITEMLENGTHVALUE0":"   0.00000","L_ITEMWIDTHVALUE0":"   0.00000","L_ITEMHEIGHTVALUE0":"   0.00000","PAYMENTREQUEST_0_CURRENCYCODE":"USD","PAYMENTREQUEST_0_AMT":"5.99","PAYMENTREQUEST_0_ITEMAMT":"5.99","PAYMENTREQUEST_0_SHIPPINGAMT":"0.00","PAYMENTREQUEST_0_HANDLINGAMT":"0.00","PAYMENTREQUEST_0_TAXAMT":"0.00","PAYMENTREQUEST_0_DESC":"Sample item","PAYMENTREQUEST_0_INSURANCEAMT":"0.00","PAYMENTREQUEST_0_SHIPDISCAMT":"0.00","PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED":"false","L_PAYMENTREQUEST_0_NAME0":"Sample item","L_PAYMENTREQUEST_0_NUMBER0":"order-6","L_PAYMENTREQUEST_0_QTY0":"1","L_PAYMENTREQUEST_0_TAXAMT0":"0.00","L_PAYMENTREQUEST_0_AMT0":"23.00","L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE0":"   0.00000","L_PAYMENTREQUEST_0_ITEMLENGTHVALUE0":"   0.00000","L_PAYMENTREQUEST_0_ITEMWIDTHVALUE0":"   0.00000","L_PAYMENTREQUEST_0_ITEMHEIGHTVALUE0":"   0.00000"}',
						'data_returned' => '{"TOKEN":"EC-3CC324611K842411K","SUCCESSPAGEREDIRECTREQUESTED":"false","TIMESTAMP":"2014-10-02T19:16:26Z","CORRELATIONID":"11a6663cxo30d","ACK":"Success","VERSION":"63.0","BUILD":"3719653","INSURANCEOPTIONSELECTED":"false","SHIPPINGOPTIONISDEFAULT":"false","PAYMENTINFO_0_TRANSACTIONID":"4WN12370L1021013D","PAYMENTINFO_0_TRANSACTIONTYPE":"expresscheckout","PAYMENTINFO_0_PAYMENTTYPE":"instant","PAYMENTINFO_0_ORDERTIME":"2014-10-02T19:16:23Z","PAYMENTINFO_0_AMT":"5.99","PAYMENTINFO_0_FEEAMT":"0.35","PAYMENTINFO_0_TAXAMT":"0.00","PAYMENTINFO_0_CURRENCYCODE":"USD","PAYMENTINFO_0_PAYMENTSTATUS":"Completed","PAYMENTINFO_0_PENDINGREASON":"None","PAYMENTINFO_0_REASONCODE":"None","PAYMENTINFO_0_PROTECTIONELIGIBILITY":"Ineligible","PAYMENTINFO_0_ERRORCODE":"0","PAYMENTINFO_0_ACK":"Success"}',
						'successful' => 1,
						'gross_price' => 5.99,
						'service_fee' => 0.35,
						'currency' => 'USD',
						'status' => 'complete'
					)
				);
				// id in $cr->response['payload']

				$cust = new CASHRequest(
					array(
						'cash_request_type' => 'system', 
						'cash_action' => 'addlogin',
						'address' => 'fake@buyer.com',
						'password' => 'whocares',
						'first_name' => 'Fake',
						'last_name' => 'McTest',
						'display_name' => 'Fake McTest'
					)
				);

				$ord = new CASHRequest(
					array(
						'cash_request_type' => 'commerce', 
						'cash_action' => 'addorder',
						'user_id' => 1,
						'customer_user_id' => $cust->response['payload'],
						'transaction_id' => $cr->response['payload'],
						'order_contents' => array(
							array(
								'id' => 1,
								'user_id' => 1,
								'name' => 'Sample item',
								'description' => 'This is a description for the test item.',
								'sku' => '#abc123',
								'price' => '5.99',
								'flexible_price' => 0,
								'digital_fulfillment' => 1,
								'physical_fulfillment' => 0,
								'physical_weight' => 0,
								'physical_width' => 0,
								'physical_height' => 0,
								'physical_depth' => 0,
								'available_units' => -1,
								'variable_pricing' => 0,
								'fulfillment_asset' => 1,
								'descriptive_asset' => 0,
								'creation_date' => time()
							)
						),
						'fulfilled' => 0,
						'canceled' => 0,
						'physical' => 0,
						'digital' => 1,
						'notes' => '',
						'country_code' => 'US',
						'currency' => 'USD',
						'element_id' => 1,
					)
				);

				// this is silly, but we need to edit the order to get a positive "modification_date"
				// in the table — easiest way to test for abandoned/non-abandoned. will probably be 
				// changing that behavior later.
				new CASHRequest(
					array(
						'cash_request_type' => 'commerce', 
						'cash_action' => 'editorder',
						'id' => $ord->response['payload'],
						'fulfilled' => 1,
					)
				);


				$cr = new CASHRequest(
					array(
						'cash_request_type' => 'calendar', 
						'cash_action' => 'addvenue',
						'name' => 'Tiga', 
						'city' => 'Portland',
						'region' => 'OR'
					)
				);
				$venue_id = $cr->response['payload'];

				new CASHRequest(
					array(
						'cash_request_type' => 'calendar', 
						'cash_action' => 'addevent', 
						'date' => 1388534400,
						'user_id' => 1,
						'venue_id' => $venue_id,
						'published' => 1,
						'comment' => 'past event'
					)
				);

				new CASHRequest(
					array(
						'cash_request_type' => 'calendar', 
						'cash_action' => 'addevent', 
						'date' => time() + 5184000,
						'user_id' => 1,
						'venue_id' => $venue_id,
						'published' => 1,
						'comment' => 'coming event'
					)
				);

				$cr = new CASHRequest(
					array(
						'cash_request_type' => 'element', 
						'cash_action' => 'addelement', 
						'name' => 'Test element',
						'type' => 'emailcollection',
						'options_data' => array(
							'message_invalid_email' => 'Sorry, that email address wasn\'t valid. Please try again.',
							'message_privacy' => 'We won\'t share, sell, or be jerks with your email address.',
							'message_success' => 'Thanks! You\'re all signed up.',
							'email_list_id' => $list_id,
							'asset_id' => $file_id,
							'comment_or_radio' => 0,
							'do_not_verify' => 1
						),
						'user_id' => 1
					)
				);

				?>
					<h1>Success. There's a whole bunch of junk stuffed in there now.</h1>
				<?php
			} else {
		?>
				<form method="post" action="">
					<input type="hidden" name="noreallygo" value="1" />
					<button type="submit"><i class='icon icon-cogs'></i>fill things up</button>
				</form>
		<?php 
			}
		?>
		</div>

		<br /><br /><br />
	</div>

	<div id="bottomspc"></div>
</body> 
</html>