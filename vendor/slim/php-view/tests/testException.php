Hello world. You should not see this because there is an error in the template.
<?php
$exception = new Exception('An error occurred');
if($exception instanceof Throwable) {
    echo 2/0;
}

throw $exception;
?>
