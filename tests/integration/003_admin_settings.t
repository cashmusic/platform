#!/usr/bin/env perl
use strict;
use warnings;
use Test::Most;
use Test::WWW::Mechanize;
use File::Spec::Functions;
use lib catdir(qw/tests lib/);
use Test::Cashmusic qw/mech/;

my $base = $ENV{CASHMUSIC_TEST_URL} || 'http://localhost:80';

BEGIN {
    # Run the test installer every time we run these tests
    qx "php installers/php/test_installer.php";
}

my @settings = qw/mailchimp amazon twitter/;
map { mech->get_ok("$base/interfaces/php/admin/settings/add/com.$_") } @settings;

done_testing();
