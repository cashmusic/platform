#!/usr/bin/env perl

use strict;
use warnings;
use File::Spec::Functions;
use lib catdir(qw/tests lib/);
use Test::Cashmusic qw/mech login_ok mech_success_ok json_ok/;
use Test::Most;
use JSON::Any;
#use Carp::Always;

my $j = JSON::Any->new;
my $base = $ENV{CASHMUSIC_TEST_URL} || 'http://localhost:80';

sub test_basic {
    my (@methods) = @_;
    for my $method (@methods) {
        my $json = json_ok("$base/interfaces/php/api/", $method);

        my $response = $j->from_json($json);
        cmp_ok($response->{api_version},'>=',1,'got an API version >= 1');
        cmp_ok($response->{greeting},'eq','hi.','was greeted properly');
    }
}

sub test_basic_verbose {
    my (@methods) = @_;
    for my $method (@methods) {
        my $json = json_ok("$base/interfaces/php/api/verbose", $method);

        my $response = $j->from_json($json);
        cmp_ok($response->{status_code},'==',400,'got a 400 status_code');
        cmp_ok($response->{api_version},'>=',1,'got an API version >= 1');
        cmp_ok($response->{timestamp},'>=',0,'got an non-zero timestamp');
        ok( defined $response->{contextual_message}, 'contextual_message is present' );
        ok( defined $response->{status_message}, 'status_message is present' );
    }
}

sub test_basic_invalid {
    my (@methods) = @_;
    for my $method (@methods) {
        my $json = json_ok("$base/interfaces/php/api/junk", $method);

        my $response = $j->from_json($json);
        cmp_ok($response->{api_version},'>=',1,'got an API version >= 1');
        cmp_ok($response->{greeting},'eq','hi.','was greeted properly');
    }
}

sub test_processwebhook {
    my (@methods) = @_;
    for my $method (@methods) {
        my $key      = "42";
        my $url      = "$base/interfaces/php/api/verbose/people/processwebhook/origin/com.mailchimp/list_id/100/api_key/$key";
        # http://apidocs.mailchimp.com/webhooks/
        my $jdata    = $j->to_json({
            type => 'subscribe',
            fired_at => "2009-03-26 21:35:57",
            data => {
                id         => "8a25ff1d98", # what is this used for?
                list_id    => "b607c6d911", # cash music testing list
                email      => 'billybob@aol.com',
                email_type => 'html',
                ip_opt     => '10.10.10.10',
                ip_signup  => '10.10.10.10',
            },
        });
        my $json     = json_ok($url, $method, $jdata);
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
        my $json = json_ok($url, $method);

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
        my $json = json_ok($url, $method);

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
        my $json = json_ok($url, $method);

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
        my $json = json_ok($url, $method);

        my $response = $j->from_json($json);
        cmp_ok($response->{status_code},'==',400,"$method 400 status_code from getlistinfo");
        cmp_ok($response->{contextual_message},'ne','unknown action','contextual_message != unknown action');
        cmp_ok($response->{request_type},'eq','people','request_type = people');
        cmp_ok($response->{action},'eq','getlistinfo','action = getlistinfo');
    }
}

sub test_verbose_system {
    my (@methods) = @_;
    for my $method (@methods) {
        my $json     = json_ok("$base/interfaces/php/api/verbose/system/", $method);
        my $response = $j->from_json($json);
        cmp_ok($response->{status_code},'==',400,'got a 400 status_code');
        cmp_ok($response->{api_version},'>=',1,'got an API version >= 1');
        cmp_ok($response->{timestamp},'>=',0,'got an non-zero timestamp');
    }
}

# actually run tests

my @methods = qw/get post/;

test_basic(@methods);
test_basic_verbose(@methods);
test_basic_invalid(@methods);
test_processwebhook(@methods);
test_processwebhook_invalid_key(@methods);
test_getlistinfo(@methods);
test_getlistinfo_nonexistent(@methods);
test_getlistinfo_on_somebody_elses_list(@methods);
test_verbose_system(@methods);

done_testing;
