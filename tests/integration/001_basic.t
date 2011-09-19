#!/usr/bin/env perl
use strict;
use warnings;
use Test::Most tests => 5;
use Test::WWW::Mechanize;

my $base = $ENV{CASHMUSIC_TEST_URL} || 'http://localhost:80';
my $mech = Test::WWW::Mechanize->new;

my @urls = qw{/ /interfaces/php/admin/ /interfaces/php/demos /interfaces/php/demos/emailcontestentry/ /interfaces/php/demos/emailfordownload/};

# Basic tests that verify the above URLs all return a 200
for my $url (@urls) {
    $mech->get_ok("$base$url");
}
