<?php

namespace CASHMusic\Elements\Subscription;

use CASHMusic\Core\CASHSystem;
use CASHMusic\Core\ElementBase;
use CASHMusic\Core\CASHRequest;

class Subscription extends ElementBase {
	public $type = 'subscription';
	public $name = 'Subscription';

	public function getData() {

        // set state and fire the appropriate method in Element\State class
        $state = new ElementState(
            $this->element_data,
            $this->session_id
        );

        $state->router(function ($template, $values) {
            $this->setTemplate($template);
            $this->updateElementData($values);
        });


		return $this->element_data;
	}
} // END class
?>