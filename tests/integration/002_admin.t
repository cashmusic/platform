#!/usr/bin/env perl

use strict;
use warnings;
use Test::Most;
use Test::JSON;
use Test::WWW::Mechanize;
use File::Spec::Functions;
use lib catdir(qw/tests lib/);
use Test::Cashmusic qw/mech/;
#use Carp::Always;

my $base = $ENV{CASHMUSIC_TEST_URL} || 'http://localhost:80';

mech->get_ok("$base/interfaces/php/admin/");
mech->content_contains('email');
mech->content_contains('password');
mech->content_contains('CASH Music');
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


my @admin_urls    = qw{
    settings commerce elements assets calendar
    people people/mailinglists people/mailinglists/view people/mailinglists/export people/mailinglists/add
    help help/gettingstarted elements/add/socialfeeds
};
my @metadata_urls = map { "components/elements/$_/metadata.json" } qw{emailcollection tourdates};

for my $url (@admin_urls) {
    mech->get_ok("$base/interfaces/php/admin/$url");
}

for my $url (@metadata_urls) {
    my $full_url = "$base/interfaces/php/admin/$url";
    mech->get_ok($full_url);
    is_valid_json(mech->content, "$full_url is valid JSON");
}

mech->get_ok("$base/interfaces/php/admin/elements/view/100");
mech->content_contains("Portugal. The Man");
mech->content_contains('CASHSystem::embedElement(100)');

mech->get_ok("$base/interfaces/php/admin/elements/view/101");
mech->content_contains("Iron & Wine");
mech->content_contains('CASHSystem::embedElement(101)');

mech->get_ok("$base/interfaces/php/admin/elements/view/102");
mech->content_contains("Wild Flag");
mech->content_contains('CASHSystem::embedElement(102)');

mech->get_ok("$base/interfaces/php/admin/elements/view/103");
mech->content_contains('Palmer/Gaiman filtered social feeds');
mech->content_contains('CASHSystem::embedElement(103)');

mech->get_ok("$base/interfaces/php/admin/elements/delete/101");
mech->submit_form_ok({
    form_number => 1,
    fields => {
        doelementdelete => "makeitso",
    },
}, 'delete element form');

# Look for errors like
# SQLSTATE[HY000]: General error: 8 attempt to write a readonly database
mech->content_unlike(qr/SQLSTATE.*error/i);

mech->get_ok("$base/interfaces/php/admin/assets/add/playlist/");
mech->content_contains('Add Playlist');

mech->get_ok("$base/interfaces/php/admin/assets/add/single/");
mech->submit_form_ok({
    form_number => 1,
    fields      => {
        asset_description => 'asdf',
        asset_location    => 'http://aasdf.com',
        asset_title       => 'adsf',
        doassetadd        => 'makeitso',
        settings_id       => 0
    },
}, 'add asset form');
mech->content_unlike(qr/Error/);
mech->content_contains('Success');

mech->get_ok("$base/interfaces/php/admin/elements/add/emailcollection");
mech->submit_form_ok({
    form_name   => 'emailcollection',
    fields      => {
        asset_id         =>   '100',
        comment_or_radio =>   'none',
        doelementadd     =>   'makeitso',
        element_name     =>   'foooobaf',
        element_type     =>   'emailcollection',
        emal_list_id     =>   '100',
        message_invalid_email =>  "Sorry, that email address wasn't valid. Please try again.",
        message_privacy  => "We won't share, sell, or be jerks with your email address.",
        message_success  => "Thanks! You're all signed up. Here's your download",
   },
}, 'add email collection form');
mech->content_like(qr/Success/);

mech->get_ok("$base/interfaces/php/admin/elements/add/tourdates");
mech->submit_form_ok({
    form_name   => "tourdates",
    fields      => {
        doelementadd        => 'makeitso',
        element_name        => 'Nyarlathotep Live Tour',
        element_type        => 'tourdates',
        visible_event_types => 'upcoming',
    },
}, 'add tourdates form');
mech->content_unlike(qr/Error/);
mech->content_like(qr/Success/);

mech->get("$base/interfaces/php/admin/people/lists");
mech->submit_form_ok({
    form_number => 1,
    fields      => {
        dolistadd        =>   'makeitso',
        list_description =>   'Ultharian cats',
        list_name        =>   'cats_of_ulthar',
        settings_id      =>   0,
    },
}, 'add mailing list') or diag mech->content;
mech->content_like(qr/Success/);

mech->get("$base/interfaces/php/admin/elements/add/socialfeeds");
mech->submit_form_ok({
    form_name => 'socialfeeds',
    fields      => {
        doelementadd        => 'makeitso',
        element_name        => 'miskatonic_fireside_chat',
        element_type        => 'socialfeeds',
        tumblrurl1          => 'http://example.com/tumblr',
        twitterusername1    => '@bob',
        twitterusername2    => '@alice',
        twitterfiltertype1  => 'beginwith',
        twitterfiltertype2  => 'contain',
        twitterfiltervalue1 => 'FOO',
        twitterfiltervalue2 => 'BAR',
    },
}, 'add social feeds');
mech->content_like(qr/Success/);

done_testing;
