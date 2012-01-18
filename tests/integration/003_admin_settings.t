#!/usr/bin/env perl
use strict;
use warnings;
use Test::Most;
use Test::WWW::Mechanize;
use File::Spec::Functions;
use lib catdir(qw/tests lib/);
use Test::Cashmusic qw/mech/;

my $base = $ENV{CASHMUSIC_TEST_URL} || 'http://localhost:80';

my @settings = qw/mailchimp amazon twitter/;
map { mech->get_ok("$base/interfaces/php/admin/settings/add/com.$_") } @settings;

mech->get_ok("$base/interfaces/php/admin/settings/add/com.mailchimp");
mech->submit_form_ok({
    form_number => 1,
    fields      => {
        dosettingsadd => 'makeitso',
        settings_type => 'com.mailchimp',
        settings_name => 'Arnold Classic',
        key           => 'decafbad',
        list          => 'governator_list',
    },
}, 'add mailchimp connection');
mech->get_ok("$base/interfaces/php/admin/settings/");

done_testing();
