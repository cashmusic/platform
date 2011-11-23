package Test::Cashmusic;

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

sub mech {
    $mech
}

sub mech_success_ok {
    mech->content_like(qr/Success/) or diag mech->content;
}

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

sub json_ok {
    my ($url) = @_;
    mech->get($url);
    my $json = mech->content;
    is_valid_json($json,"$url is valid JSON");
    diag $json if $ENV{CASHMUSIC_DEBUG_JSON};
    # return the JSON for further testing
    return $json;
}


1;
