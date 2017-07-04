<?php
use Kahlan\Filter\Filter;
use Kahlan\Reporter\Coverage;
use Kahlan\Reporter\Coverage\Driver\Xdebug;
use Kahlan\Reporter\Coverage\Exporter\Coveralls;
use Kahlan\Reporter\Coverage\Exporter\CodeClimate;

$commandLine = $this->commandLine();
$commandLine->option('coverage', 'default', 3);

Filter::register('kahlan.coverage', function($chain) {
    if (!extension_loaded('xdebug')) {
        return;
    }
    $reporters = $this->reporters();
    $coverage = new Coverage([
        'verbosity' => $this->commandLine()->get('coverage'),
        'driver'    => new Xdebug(),
        'path'      => $this->commandLine()->get('src'),
        'exclude'   => [
            //Exclude init script
            'src/init.php',
            'src/functions.php',
            //Exclude Workflow from code coverage reporting
            'src/Cli/Kahlan.php',
            //Exclude coverage classes from code coverage reporting (don't know how to test the tester)
            'src/Reporter/Coverage/Collector.php',
            'src/Reporter/Coverage/Driver/Xdebug.php',
            'src/Reporter/Coverage/Driver/HHVM.php',
            'src/Reporter/Coverage/Driver/Phpdbg.php',
            //Exclude text based reporter classes from code coverage reporting (a bit useless)
            'src/Reporter/Dot.php',
            'src/Reporter/Bar.php',
            'src/Reporter/Verbose.php',
            'src/Reporter/Terminal.php',
            'src/Reporter/Reporter.php',
            'src/Reporter/Coverage.php',
            'src/Reporter/Json.php',
            'src/Reporter/Tap.php',
        ],
        'colors'    => !$this->commandLine()->get('no-colors')
    ]);
    $reporters->add('coverage', $coverage);
});

Filter::apply($this, 'coverage', 'kahlan.coverage');

Filter::register('kahlan.coverage-exporter', function($chain) {
    $reporter = $this->reporters()->get('coverage');
    if (!$reporter) {
        return;
    }
    Coveralls::write([
        'collector'      => $reporter,
        'file'           => 'coveralls.json',
        'service_name'   => 'travis-ci',
        'service_job_id' => getenv('TRAVIS_JOB_ID') ?: null
    ]);
    CodeClimate::write([
        'collector'  => $reporter,
        'file'       => 'codeclimate.json',
        'branch'     => getenv('TRAVIS_BRANCH') ?: null,
        'repo_token' => '422174e17459424c0dc0dfdd720eb17324b283a204b093e85ba21400f1414536'
    ]);
    return $chain->next();
});

Filter::apply($this, 'reporting', 'kahlan.coverage-exporter');
