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
sub test_processwebhook {
    my (@methods) = @_;
    for my $method (@methods) {
        my $key = "42";
        mech->$method("$base/interfaces/php/api/verbose/people/processwebhook/api_key/$key");
        my $json = mech->content;
        is_valid_json($json, 'processwebhook json');
        #diag $json;

        my $response = $j->from_json($json);
        cmp_ok($response->{status_code},'==',200,"$method 200 status_code from processwebhook");
        cmp_ok($response->{contextual_message},'ne','unknown action','contextual_message != unknown action');
        cmp_ok($response->{request_type},'eq','people','request_type = people');
        cmp_ok($response->{action},'eq','processwebhook','action = processwebhook');
    }
}


sub test_getlistinfo {
    my (@methods) = @_;
    for my $method (@methods) {
        my $key = "42";
        mech->$method("$base/interfaces/php/api/verbose/people/getlistinfo/api_key/$key/id/100");
        my $json = mech->content;
        is_valid_json($json, 'getlistinfo json');
        #diag $json;

        my $response = $j->from_json($json);
        cmp_ok($response->{status_code},'==',200,"$method 200 status_code from getlistinfo");
        cmp_ok($response->{contextual_message},'ne','unknown action','contextual_message != unknown action');
        cmp_ok($response->{request_type},'eq','people','request_type = people');
        cmp_ok($response->{action},'eq','getlistinfo','action = getlistinfo');
    }
}

my @methods = qw/get post/;
test_processwebhook(@methods);
test_getlistinfo(@methods);

{
    mech->get("$base/interfaces/php/api/verbose/system/");
    my $json = mech->content;
    is_valid_json($json, 'valid json');
    #diag $json;

    my $response = $j->from_json($json);
    cmp_ok($response->{status_code},'==',400,'got a 400 status_code');
    cmp_ok($response->{api_version},'>=',1,'got an API version >= 1');
    cmp_ok($response->{timestamp},'>=',0,'got an non-zero timestamp');
}
done_testing;
