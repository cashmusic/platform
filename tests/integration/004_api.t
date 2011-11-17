#!/usr/bin/env perl

use strict;
use warnings;
use File::Spec::Functions;
use lib catdir(qw/tests lib/);
use Test::Cashmusic qw/mech login_ok mech_success_ok/;
use Test::Most;
use Test::JSON;
use JSON::Any;
#use Carp::Always;

my $j = JSON::Any->new;
my $base = $ENV{CASHMUSIC_TEST_URL} || 'http://localhost:80';

{
    mech->get("$base/interfaces/php/api/verbose");

    my $json = mech->content;
    is_valid_json($json, 'invalid query still returns valid json');
#diag $json;

    my $response = $j->from_json($json);
    cmp_ok($response->{status_code},'==',400,'got a 400 status_code');
    cmp_ok($response->{api_version},'>=',1,'got an API version >= 1');
    cmp_ok($response->{timestamp},'>=',0,'got an non-zero timestamp');
    ok( defined $response->{contextual_message}, 'contextual_message is present' );
    ok( defined $response->{status_message}, 'status_message is present' );
}
{
    # TODO: the api_key needs to be set properly
    mech->get("$base/interfaces/php/api/verbose/system/processwebhook/api_key/19c13");
    my $json = mech->content;
    is_valid_json($json, 'processwebhook json');
    diag $json;

    my $response = $j->from_json($json);
    cmp_ok($response->{status_code},'==',200,'got a 200 status_code from processwebhook');
}

done_testing;
