<?php
$cash_admin->page_data['country_codes'] = AdminHelper::drawCountryCodeUL();

CASHSystem::sendEmail(
	'Hi there.',
	CASHSystem::getDefaultEmail(),
	'jessevondoom@gmail.com',
	'This is just a stupid fucking test, but you know that already.',
	'Hello'
);

$cash_admin->setPageContentTemplate('people_contacts');
?>