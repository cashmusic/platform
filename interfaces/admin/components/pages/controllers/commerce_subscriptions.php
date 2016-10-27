<pre>
    <?php

    $stripe = new StripeSeed(1, 11);

    /*$stripe->createSubscriptionPlan(
        "mold",
        $amount=100,
        $interval="year",
        $currency="usd"
    );*/

    //$stripe->updateSubscriptionPlan("mold", "goldie farts");
    $stripe->deleteSubscriptionPlan("mold");
    $goldie = $stripe->getSubscriptionPlan("mold");

    var_dump($goldie);
    ?>
</pre>