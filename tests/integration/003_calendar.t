#!/usr/bin/env perl

use strict;
use warnings;
use File::Spec::Functions;
use lib catdir(qw/tests lib/);
use Test::Cashmusic qw/mech login_ok mech_success_ok/;
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

mech_success_ok;

mech->get_ok("$base/interfaces/php/admin/calendar/venues/");
# make sure Backspace shows up on the list of venues
mech->content_like(qr/Backspace/);

# make sure Backspace shows up on the list of venues an event can be at
mech->get_ok("$base/interfaces/php/admin/calendar/events/");
mech->content_like(qr/Backspace/);

mech->submit_form_ok({
    form_name   => "add_event",
    fields      => {
        doeventadd         => 'makeitso',
        event_date         => '01-01-2012',
        event_venue        => 114,
        event_comment      => "blarg",
        event_purchase_url => "http://cashmusic.org/tickets",
        event_ispublished  => 1,
        event_canceled     => 0,
    },
}, 'quick add event');

mech_success_ok;

done_testing;
