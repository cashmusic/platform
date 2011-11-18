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
        my $url = "$base/interfaces/php/api/verbose/people/processwebhook/origin/com.mailchimp/list_id/100/api_key/$key";
        mech->$method($url);
        my $json = mech->content;
        is_valid_json($json, "$url returns valid json");
        #diag $json;

        my $response = $j->from_json($json);
        { local $TODO = "returns 400 instead of 200";
        cmp_ok($response->{status_code},'==',200,"$method 200 status_code from processwebhook");
        }
        cmp_ok($response->{contextual_message},'ne','unknown action','contextual_message != unknown action');
        cmp_ok($response->{request_type},'eq','people','request_type = people');
        cmp_ok($response->{action},'eq','processwebhook','action = processwebhook');
    }
}

sub test_processwebhook_invalid_key {
    my (@methods) = @_;
    for my $method (@methods) {
        my $key = "69";
        my $url = "$base/interfaces/php/api/verbose/people/processwebhook/origin/com.mailchimp/list_id/100/api_key/$key";
        mech->$method($url);
        my $json = mech->content;
        is_valid_json($json, "$url with invalid key returns valid json");
        #diag $json;

        my $response = $j->from_json($json);
        cmp_ok($response->{status_code},'==',403,"$method 403 status_code from processwebhook");
        cmp_ok($response->{contextual_message},'ne','unknown action','contextual_message != unknown action');
        cmp_ok($response->{request_type},'eq','people','request_type = people');
        cmp_ok($response->{action},'eq','processwebhook','action = processwebhook');
    }
}


sub test_getlistinfo {
    my (@methods) = @_;
    for my $method (@methods) {
        my $key = "42";
        my $url = "$base/interfaces/php/api/verbose/people/getlistinfo/api_key/$key/id/100";
        mech->$method($url);
        my $json = mech->content;
        is_valid_json($json, "$url returns valid json");
        #diag $json;

        my $response = $j->from_json($json);
        cmp_ok($response->{status_code},'==',200,"$method 200 status_code from getlistinfo");
        cmp_ok($response->{contextual_message},'ne','unknown action','contextual_message != unknown action');
        cmp_ok($response->{request_type},'eq','people','request_type = people');
        cmp_ok($response->{action},'eq','getlistinfo','action = getlistinfo');
    }
}

sub test_getlistinfo_nonexistent {
    my (@methods) = @_;
    for my $method (@methods) {
        my $key = "42";
        my $url = "$base/interfaces/php/api/verbose/people/getlistinfo/api_key/$key/id/69";
        mech->$method($url);
        my $json = mech->content;
        is_valid_json($json, "$url returns valid json");
        #diag $json;

        my $response = $j->from_json($json);
        cmp_ok($response->{status_code},'==',400,"$method 400 status_code from getlistinfo");
        cmp_ok($response->{contextual_message},'ne','unknown action','contextual_message != unknown action');
        cmp_ok($response->{request_type},'eq','people','request_type = people');
        cmp_ok($response->{action},'eq','getlistinfo','action = getlistinfo');
    }
}

sub test_getlistinfo_on_somebody_elses_list {
    my (@methods) = @_;
    for my $method (@methods) {
        my $key = "42";
        # list 99 is owned by a non-existent user
        my $url = "$base/interfaces/php/api/verbose/people/getlistinfo/api_key/$key/id/99";
        mech->$method($url);
        my $json = mech->content;
        is_valid_json($json, "$url returns valid json");
        diag $json;

        my $response = $j->from_json($json);
        cmp_ok($response->{status_code},'==',400,"$method 400 status_code from getlistinfo");
        cmp_ok($response->{contextual_message},'ne','unknown action','contextual_message != unknown action');
        cmp_ok($response->{request_type},'eq','people','request_type = people');
        cmp_ok($response->{action},'eq','getlistinfo','action = getlistinfo');
    }
}

my @methods = qw/get post/;
test_processwebhook(@methods);
test_processwebhook_invalid_key(@methods);
test_getlistinfo(@methods);
test_getlistinfo_nonexistent(@methods);
test_getlistinfo_on_somebody_elses_list(@methods);

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
