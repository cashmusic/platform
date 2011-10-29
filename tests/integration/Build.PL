use strict;
use warnings;
use Module::Build;

# This file exists solely to make it easy to install dependencies for
# our integration tests.

my $builder = Module::Build->new(
    module_name         => 'CASH::Tests',
    license             => 'perl',
    dist_author         => 'Jonathan "Duke" Leto <jonathan@leto.net>',
    dist_abstract       => 'Integration tests for CASH Music',
    dist_version        => '0.01',
    include_dirs        => '',
    requires            => {
        autodie => 0,
    },
    build_requires => {
        'Test::Most'           => 0,
        'Test::JSON'           => 0,
        'Test::WWW::Mechanize' => '1.38',
    },
);

$builder->create_build_script();
print "Have a great day!\n";
