# Copyright (C) 2011-2012, Leto Labs, LLC
package Test::Cashmusic;

=head1 NAME

Test::Cashmusic - Convenience testing functions for CASH Music

=head1 SYNOPSIS

    # import testing functions into current scope
    use Test::Cashmusic qw/mech mech_success_ok login_ok json_ok/;

    my $m = mech();

    # pass a test if we can login properly
    login_ok();

    # tests that require logging in (i.e. admin panel)
    $m->get_ok("http://localhost:80/admin/foo/bar/baz");

=head1 DESCRIPTION

This Perl 5 module contains function which make it easier to write
Perl integration tests for CASH Music.

=head1 AUTHOR

Jonathan "Duke" Leto

=cut

use strict;
use warnings;
use autodie;
use Test::WWW::Mechanize;
use Test::Most;
use Test::JSON;
use parent 'Exporter';
#use Carp::Always;
our @EXPORT_OK = qw/mech login_ok mech_success_ok json_ok/;

my $base = $ENV{CASHMUSIC_TEST_URL} || 'http://localhost:80';
# This is temporary until I add a feature to allow a custom lint object
my $mech = Test::WWW::Mechanize->new(autolint => 0);

BEGIN {
    # Run the test installer every time we run these tests
    qx "php installers/php/test_installer.php";
}

=head2 mech

    my $m = mech();

Returns a Test::WWW::Mechanize object.

=cut

sub mech {
    $mech
}

=head2 mech_sucess_ok

    mech_success_ok();

Passes a test if the most recent content contains the string 'Success'.

=cut

sub mech_success_ok {
    mech->content_like(qr/Success/) or diag mech->content;
}

=head2 login_ok

    login_ok();

Passes some tests assuring that we can login properly.

=cut

sub login_ok {
    mech->get_ok("$base/interfaces/php/admin/");
    mech->submit_form_ok({
        form_number => 1,
        fields      => {
            # these are specified in the test installer
            address  => 'root@localhost',
            password => 'hack_my_gibson',
            login    => 1,
        },
    }, 'log in to admin area');
    mech->content_unlike(qr/Try Again/);
    return mech;
}

=head2 json_ok

    my ($url,$method) = ("http://localhost:80","get");

    # Pass/fail a test if GETting $url is valid json
    # It also returns the json for further testing
    my $json = json_ok($url, $method);

    # Pass/fail a test if POSTing $data to $url is valid json
    my ($url,$method, $data) = ("http://localhost:80","post", "foobar");
    my $json = json_ok($url, $method, $data);

Passes a test if the specified $url returns valid JSON.

=cut

sub json_ok {
    my ($url, $method, $data) = @_;
    $method ||= 'get';
    $data ? mech->$method($url, Content => $data) : mech->$method($url);
    my $json = mech->content;
    is_valid_json($json,"$method $url is valid JSON");
    diag $json if $ENV{CASHMUSIC_DEBUG_JSON};
    # return the JSON for further testing
    return $json;
}

1;
