<?php
namespace Kahlan\Spec\Suite;

use Kahlan\Log;
use Kahlan\Summary;

describe("Summary", function () {

    beforeEach(function () {

        $this->result = new Summary();

    });

    describe("->__construct()", function () {

        it("correctly sets default values", function () {

            expect($this->result->total())->toBe(0);
            expect($this->result->executable())->toBe(0);
            expect($this->result->expectation())->toBe(0);
            expect($this->result->passed())->toBe(0);
            expect($this->result->pending())->toBe(0);
            expect($this->result->skipped())->toBe(0);
            expect($this->result->excluded())->toBe(0);
            expect($this->result->failed())->toBe(0);
            expect($this->result->errored())->toBe(0);
            expect($this->result->get('focused'))->toBe([]);
            expect($this->result->logs())->toBe([]);
            expect($this->result->logs('passed'))->toBe([]);
            expect($this->result->logs('pending'))->toBe([]);
            expect($this->result->logs('skipped'))->toBe([]);
            expect($this->result->logs('excluded'))->toBe([]);
            expect($this->result->logs('failed'))->toBe([]);
            expect($this->result->logs('errored'))->toBe([]);
            expect($this->result->memoryUsage())->toBe(0);

        });

    });

    describe("->total()", function () {

        it("gets the total number of specs", function () {

            $this->result->log(new Log(['type' => 'passed']));
            $this->result->log(new Log(['type' => 'pending']));
            $this->result->log(new Log(['type' => 'skipped']));
            $this->result->log(new Log(['type' => 'excluded']));
            $this->result->log(new Log(['type' => 'failed']));
            $this->result->log(new Log(['type' => 'errored']));

            expect($this->result->total())->toBe(6);

        });

    });

    describe("->expectation()", function () {

        it("gets the total number of expectations", function () {

            $log1 = new Log();
            $log1->add('passed', []);
            $log1->add('passed', []);

            $log2 = new Log();
            $log2->add('failed', []);
            $log1->add('passed', []);
            $log2->add('failed', []);
            $log2->add('failed', []);
            $log2->add('failed', []);

            $this->result->log($log1);
            $this->result->log($log2);

            expect($this->result->expectation())->toBe(7);

        });

    });

    describe("->__call()", function () {

        it("gets number of passed specs", function () {

            $this->result->log(new Log(['type' => 'passed']));
            expect($this->result->passed())->toBe(1);

        });

        it("gets number of pending specs", function () {

            $this->result->log(new Log(['type' => 'pending']));
            expect($this->result->pending())->toBe(1);

        });

        it("gets number of skipped specs", function () {

            $this->result->log(new Log(['type' => 'skipped']));
            expect($this->result->skipped())->toBe(1);

        });

        it("gets number of excluded specs", function () {

            $this->result->log(new Log(['type' => 'excluded']));
            expect($this->result->excluded())->toBe(1);

        });

        it("gets number of failed specs", function () {

            $this->result->log(new Log(['type' => 'failed']));
            expect($this->result->failed())->toBe(1);

        });

        it("gets number of errored specs", function () {

            $this->result->log(new Log(['type' => 'errored']));
            expect($this->result->errored())->toBe(1);

        });

    });

    describe("->add()/->get()", function () {

        it("adds some custom data", function () {

            $value1 = 'value1';
            $value2 = 'value2';

            $this->result->add('focused', $value1);
            $this->result->add('focused', $value2);

            expect($this->result->get('focused'))->toBe([
                $value1,
                $value2,
            ]);

        });

    });

    describe("->logs()", function () {

        it("returns the total number of specs of a specific type", function () {

            $this->result->log(new Log(['type' => 'passed']));
            $this->result->log(new Log(['type' => 'passed']));
            $this->result->log(new Log(['type' => 'passed']));
            $this->result->log(new Log(['type' => 'pending']));
            $this->result->log(new Log(['type' => 'skipped']));
            $this->result->log(new Log(['type' => 'excluded']));
            $this->result->log(new Log(['type' => 'failed']));
            $this->result->log(new Log(['type' => 'errored']));

            expect($this->result->logs('passed'))->toHaveLength(3);
            expect($this->result->logs('skipped'))->toHaveLength(1);
            expect($this->result->logs('skipped'))->toHaveLength(1);
            expect($this->result->logs('excluded'))->toHaveLength(1);
            expect($this->result->logs('failed'))->toHaveLength(1);
            expect($this->result->logs('errored'))->toHaveLength(1);

        });

        it("returns all spec logs", function () {

            $this->result->log(new Log(['type' => 'passed']));
            $this->result->log(new Log(['type' => 'passed']));
            $this->result->log(new Log(['type' => 'passed']));
            $this->result->log(new Log(['type' => 'pending']));
            $this->result->log(new Log(['type' => 'skipped']));
            $this->result->log(new Log(['type' => 'excluded']));
            $this->result->log(new Log(['type' => 'failed']));
            $this->result->log(new Log(['type' => 'errored']));

            expect($this->result->logs())->toHaveLength(8);

        });

    });

    describe("->memoryUsage", function () {

        it("gets/adds some memory usage", function () {

            $this->result->memoryUsage(1024);
            expect($this->result->memoryUsage())->toBe(1024);

        });

    });

});
