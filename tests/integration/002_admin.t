#!/usr/bin/env perl
use strict;
use warnings;
use Test::Most tests => 30;
use Test::WWW::Mechanize;
use Test::JSON;

my $base = $ENV{CASHMUSIC_TEST_URL} || 'http://localhost:80';
my $mech = Test::WWW::Mechanize->new;

$mech->get_ok("$base/interfaces/php/admin/");
$mech->content_contains('email');
$mech->content_contains('password');
$mech->content_contains('CASH Music');
$mech->submit_form_ok({
    form_number => 1,
    fields      => {
        # these are specified in the test installer
        address  => 'root@localhost',
        password => 'hack_my_gibson',
        login    => 1,
    },
}, 'log in to admin area');
$mech->content_unlike(qr/Try Again/);

my @admin_urls    = qw{settings commerce people elements assets calendar help help/gettingstarted};
my @metadata_urls = map { "components/elements/$_/metadata.json" } qw{emailcollection tourdates};

for my $url (@admin_urls) {
    $mech->get_ok("$base/interfaces/php/admin/$url");
}

for my $url (@metadata_urls) {
    my $full_url = "$base/interfaces/php/admin/$url";
    $mech->get_ok($full_url);
    is_valid_json($mech->content, "$full_url is valid JSON");
}

$mech->get_ok("$base/interfaces/php/admin/elements/view/100");
$mech->content_contains("Portugal. The Man");
$mech->content_contains('cash_embedElement(100)');
$mech->get_ok("$base/interfaces/php/admin/elements/view/101");
$mech->content_contains("Iron & Wine");
$mech->content_contains('cash_embedElement(101)');
$mech->get_ok("$base/interfaces/php/admin/elements/view/102");
$mech->content_contains("Wild Flag");
$mech->content_contains('cash_embedElement(102)');

$mech->get_ok("$base/interfaces/php/admin/assets/add/single/");
$mech->get_ok("$base/interfaces/php/admin/elements/add/tourdates");
$mech->get_ok("$base/interfaces/php/admin/elements/add/emailcollection");
