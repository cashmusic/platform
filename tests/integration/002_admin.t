#!/usr/bin/env perl
use strict;
use warnings;
use Test::Most tests => 16;
use Test::WWW::Mechanize;

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

for my $url (@admin_urls, @metadata_urls) {
    $mech->get_ok("$base/interfaces/php/admin/$url");
}
