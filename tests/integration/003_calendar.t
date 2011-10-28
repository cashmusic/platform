#!/usr/bin/env perl

use strict;
use warnings;
use File::Spec::Functions;
use lib catdir(qw/tests lib/);
use Test::Cashmusic qw/mech login_ok/;
use Test::Most;
use Test::JSON;
#use Carp::Always;

my $base = $ENV{CASHMUSIC_TEST_URL} || 'http://localhost:80';

login_ok;

mech->get_ok("$base/interfaces/php/admin/calendar");
mech->content_contains('This week at a glance') or diag mech->content;
mech->content_contains('Unpublished events') or diag mech->content;

mech->get_ok("$base/interfaces/php/admin/calendar/venues/add");
mech->submit_form_ok({
    form_name   => "venue_add",
    fields      => {
        dovenueadd    => 'makeitso',
        venue_name    => 'Backspace',
        venue_city    => 'Portland',
        venue_region  => 'Oregon',
        venue_country => 'USA',
    },
}, 'quick add venue');
mech->content_like(qr/Success/) or diag mech->content;
mech->get_ok("$base/interfaces/php/admin/calendar/venues/");
mech->content_like(qr/Backspace/);

done_testing;
