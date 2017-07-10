<?php

namespace CASHMusic\Plants\Commerce\Stems;

use CASHMusic\Entities\CommerceSubscription;
use CASHMusic\Entities\CommerceSubscriptionMember;

use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Entities\CommerceTransaction;

use Exception;

trait Subscriptions {
    /* Subscription specific stuff */

    protected function createSubscriptionPlan($user_id, $connection_id, $plan_name, $description, $sku, $amount=0, $flexible_price=false, $recurring=true, $suggested_price=false, $physical=false, $interval="month", $interval_count=12, $currency="usd") {

        //TODO: load seed---> eventually we want this to dynamically switch, but for now
        $payment_seed = $this->getPaymentSeed($user_id, $connection_id);

        // create the plan on payment service (stripe for now) and get plan id
        if ($flexible_price) {
            $cent_amount = 1;
        } else {
            $cent_amount = $amount * 100;
        }

        if ($plan_id = $payment_seed->createSubscriptionPlan($plan_name, $sku, $cent_amount, $interval, $currency)) {

            $plan = $this->orm->create(CommerceSubscription::class, [
                'user_id' => $user_id,
                'name' => $plan_name,
                'description' => $description,
                'sku' => $sku,
                'price' => $amount, // as cents
                'flexible_price' => $flexible_price,
                'recurring_payment' => $recurring,
                'physical' => $physical,
                'interval' => $interval,
                'interval_count' => $interval_count,
                'suggested_price' => $suggested_price
            ]);

            if (!$plan) return false;

            return ['id'=>$sku, 'numeric_id'=>$plan->id];
        }

        return false;
    }

    public function getAllSubscriptionPlans($user_id, $limit=false) {

        if ($plans = $this->orm->findWhere(CommerceSubscription::class, ['user_id'=>$user_id], true)) {
            return $plans;
        }

        return false;
    }

    public function getSubscriptionPlan($id, $user_id=false) {

        $conditions = [
            'id' => $id
        ];

        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

        if ($plan = $this->orm->findWhere(CommerceSubscription::class, $conditions)) {
            return $plan;
        }

        return false;
    }

    public function getSubscriptionPlanBySku($sku) {

        if ($plan = $this->orm->findWhere(CommerceSubscription::class, ['sku'=>$sku])) {
            return $plan;
        }

        return false;
    }

    public function updateSubscriptionPlan($user_id, $connection_id, $id, $sku, $name, $description, $flexible_price=false, $suggested_price=false, $physical=false) {

        //TODO: load seed---> eventually we want this to dynamically switch, but for now
        $payment_seed = $this->getPaymentSeed($user_id, $connection_id);

        if ($payment_seed->updateSubscriptionPlan($sku, $name)) {

            if ($plan = $this->orm->findWhere(CommerceSubscription::class, ['user_id'=>$user_id,'id'=>$id])) {

                if ($plan->update([
                    'name' => $name,
                    'description' => $description,
                    'flexible_price' => $flexible_price,
                    'physical' => $physical,
                    'suggested_price' => $suggested_price
                ])) {
                    return $plan;
                }
            }
        }
        return false;
    }

    public function getAllSubscriptionsByPlan($id, $limit=false) {
        if ($members = $this->orm->findWhere(CommerceSubscriptionMember::class, ['subscription_id'=>$id], true)) {

            $subscribers = [];
            foreach ($members as $key=>$member) {

                $subscribers[$key] = $member->toArray();

                if ($user = $member->customer()) {
                    $subscribers[$key]['email_address'] = $user->email_address;
                }
            }

            return $subscribers;
        }

        return false;
    }

    public function deleteSubscriptionPlan($user_id, $id) {

        if ($plan = $this->orm->findWhere(CommerceSubscription::class, ['user_id'=>$user_id, 'id'=>$id])) {
            if ($plan->delete()) {
                return true;
            }
        }

        return false;
    }

    public function getSubscriptionDetails($id) {
        // we can handle this as id or by customer payment token
        if (is_numeric($id)) {
            $member = $this->orm->find(CommerceSubscriptionMember::class, $id );
        } else {
            $member = $this->orm->findWhere(CommerceSubscriptionMember::class, ['payment_identifier'=>$id] );
        }

        if ($member) {
            return $member;
        }

        return false;
    }

    public function subscriptionExists($user_id, $subscription_id) {

        if ($subscription = $this->orm->findWhere(CommerceSubscriptionMember::class, ['user_id'=>$user_id, 'subscription_id'=>$subscription_id])) {
            return $subscription;
        }

        return false;
    }

    public function getSubscriptionTransactions($id) {

        if ($transactions = $this->orm->findWhere(CommerceTransaction::class, ['parent_id' => $id, 'parent' => 'sub'], true)) {//, null, ['service_timestamp' => 'DESC']
            return $transactions;
        }

        return false;
    }

    public function createSubscription($element_id, $user_id, $price, $connection_id, $plan_id=false, $token=false, $email_address=false, $customer_name=false, $shipping_info=false, $quantity=1, $finalize_url=false) {

        $payment_seed = $this->getPaymentSeed($user_id, $connection_id);

        if ($subscription_plan = $this->getSubscriptionPlan($plan_id, $user_id)) {

            // if this plan doesn't even exist, then just quit.
            ###ERROR: plan doesn't exist
            if (empty($subscription_plan[0])) return "404";

            // if this plan is flexible then we need to calculate quantity based on the cent value of the plan.
            if ($subscription_plan[0]['flexible_price'] == 1) {

                // make sure price is equal or greater than minimum
                ###ERROR: price is less than minimum
                if ($price < $subscription_plan[0]['price']) return "402";

                $quantity = ($price*100); // price to cents, which will also be our $quantity because base price is always 1 cent for flexible
            }

            $name_split = CASHSystem::splitCustomerName($customer_name);

            $customer = [
                'customer_email' => trim(strtolower($email_address)),
                'customer_name' => trim($customer_name),
                'customer_first_name' => $name_split['first_name'],
                'customer_last_name' => $name_split['last_name'],
                'customer_countrycode' => "" // none unless there's shipping

            ];

            if ($subscriber_user_id = $this->getOrCreateUser($customer)) {

                if ($shipping_info) {

                    if (!is_array($shipping_info)) {
                        $shipping_info = json_decode($shipping_info, true);
                    }

                    $shipping_info = [
                        'customer_shipping_name' => $shipping_info['name'],
                        'customer_address1' => $shipping_info['address1'],
                        'customer_address2' => $shipping_info['address2'],
                        'customer_city' => $shipping_info['city'],
                        'customer_region' => $shipping_info['state'],
                        'customer_postalcode' => $shipping_info['postalcode'],
                        'customer_countrycode' => $shipping_info['country']
                    ];
                }

                $data = [
                    'shipping_info' => $shipping_info,
                    'customer' => $customer
                ];

                // for multi-plan element featureset we need to make sure they don't already have another plan
                // on this same element, so let's get all of the element's plans to check against first
                if (!$element_data = $this->getElementData($element_id, $user_id)) {
                    // this is a big problem, if we don't get any element data back.
                    return "412";
                }

                if (!empty($element_data['options']['plans'])) {
                    $element_plans = [];

                    foreach ($element_data['options']['plans'] as $plan) {
                        $element_plans[] = $plan['plan_id'];
                    }

                }

                // add user to subscription membership and set inactive to start, so stripe has someone to talk to
                if (!$existing_subscriptions = $this->subscriptionExists($subscriber_user_id, $element_plans)) {

                    if (!$subscription_member_id = $this->createSubscriptionMember(
                        $subscriber_user_id,
                        $subscription_plan[0]['id'],
                        $data)
                    ) {
                        ###ERROR: error creating membership
                        return "412";
                    }

                } else {

                    $subscription_member_id = false;
                    $active = false;

                    // okay, so this user has a subscription for a plan under this element. same as passed plan_id?
                    foreach($existing_subscriptions as $subscription) {

                        // keep track of which subscriptions are marked as active
                        if ($subscription['status'] == 'active') {
                            $active[$subscription['payment_identifier']] = $subscription['id'];
                        }

                        // if there's a match on passed plan, then we check if it's an active subscription
                        if ($subscription['subscription_id'] == $subscription_plan[0]['id']) {
                            // if subscription exists we need to allow them to subscribe if their status is
                            // 'canceled'. this raises some questions and problems with race conditions and
                            // double subscriptions but hey
                            if ($subscription['status'] == 'active') {
                                ###ERROR: subscriber already exists for this plan and it's active
                                return "409";
                            } else {
                                // return inactive subscription id match
                                $subscription_member_id = $subscription['id'];
                            }
                        }

                        // if not let's cancel currently active one, then subscribe to plan_id
                        if (!$subscription_member_id) {
                            if (!empty($active)) {
                                foreach($active as $payment_identifier => $active_subscription) {

                                    $payment_seed->cancelSubscription($payment_identifier);

                                    // remember to set the subscription member id
                                    $subscription_member_id = $active_subscription;
                                }
                            } else {
                                // okay, the plan passed does not match the existing subscription, and it's not active.
                                // this most likely means it's not active and we can modify it anyways.
                                // it could also mean we're in a race condition where it's in the process of being activated.
                                // we need to just operate under the assumption that they meant to do this new subscription
                                // since it doesn't match the previous plan id.
                                $subscription_member_id = $existing_subscriptions[0]['id'];
                            }

                        }

                        $this->updateSubscription($subscription_member_id, "created", false, false, $subscription_plan[0]['id']);
                    }
                }

                // create actual subscription on stripe
                if ($subscription = $payment_seed->createSubscription($token, $subscription_plan[0]['sku'], $email_address, $quantity)) {
                    // we need to add in the customer token so we can actually corollate with the webhooks

                    $member = $this->orm->find(CommerceSubscriptionMember::class, $subscription_member_id );

                    if (!$member->update(['payment_identifier'=>$subscription->id])) return "406";

                } else {
                    return "406";
                }

                $email_content = $element_data['options']['message_email'];

                if (!self::sendResetValidationEmail(
                    $element_id,
                    $user_id,
                    $email_address,
                    $finalize_url,
                    $email_content)) {
                    return "417";
                }

                return "200";

            } else {
                ###ERROR: error creating user
                return "403";
            }
        } else {
            ###ERROR: plan doesn't exist
            return "404";
        }

    }

    public function updateSubscription($id, $status=false, $total=false, $start_date=false, $update_plan_id=false) {

        $values = [];

        if ($status) {
            $values['status'] = $status;
        }

        if ($start_date) {
            $values['start_date'] = $start_date;
        }

        if ($total) {
            $values['total_paid_to_date'] = $total;
        }

        if ($update_plan_id) {
            $values['subscription_id'] = $update_plan_id;
        }

        if (count($values) < 1) return false;

        if ($member = $this->orm->find(CommerceSubscriptionMember::class, $id)) {
            if ($member->update($values)) {
                return true;
            }
        }

        return false;
    }

    public function cancelSubscription($user_id, $connection_id, $id) {

        $this->updateSubscription($id, "canceled");
        $payment_seed = $this->getPaymentSeed($user_id, $connection_id);

        $subscription = $this->getSubscriptionDetails($id);

        if(isset($subscription->payment_identifier)) {
            if ($payment_seed->cancelSubscription($subscription->payment_identifier)) {
                return true;
            }
        } else {
            return true; // whatevers for now i guess
        }


        return false;
    }

    public function deleteSubscription($id, $subscription_id) {

        if ($member = $this->orm->findWhere(CommerceSubscriptionMember::class, ['id'=>$id, 'subscription_id'=>$subscription_id])) {
            if ($member->delete()) {
                return true;
            }
        }

        return false;
    }

    public function createCompedSubscription($user_id, $plan_id, $first_name, $last_name, $email_address) {
        //
        // check if user exists by email passed, or else create a new one
        $customer = [
            'customer_email' => trim(strtolower($email_address)),
            'customer_name' => trim($first_name) . " " . trim($last_name),
            'customer_first_name' => $first_name,
            'customer_last_name' => $last_name,
            'customer_countrycode' => "" // none unless there's shipping

        ];

        $data = [
            'shipping_info' => [],
            'customer' => $customer
        ];


        if ($subscriber_user_id = $this->getOrCreateUser($customer)) {

        } else {
            return false;
        }
        $subscription_member_id = false;
        // manually create a new subscription and set to comped
        if (!$existing_subscriptions = $this->subscriptionExists($subscriber_user_id, [$plan_id])) {

            if (!$subscription_member_id = $this->createSubscriptionMember(
                $subscriber_user_id,
                $plan_id,
                $data)
            ) {
                ###ERROR: error creating membership
                return false;
            }

        }

        $this->updateSubscription($subscription_member_id, "comped", false, false, $plan_id);

        if (!self::sendResetValidationEmail(
            52,
            $user_id,
            $email_address,
            "https://family.cashmusic.org/",
            "You've been comped for a subscription. <a href=\"{{{verify_link}}}\">Click here</a> to verify your email and set a password.")) {
            return false;
        }

        return true;
    }

    public function loginSubscriber($email=false, $password=false, $plans=false) {

        $validate_request = new CASHRequest(
            array(
                'cash_request_type' => 'system',
                'cash_action' => 'validatelogin',
                'address' => $email,
                'password' => $password,
                'keep_session' => true
            )
        );

        // email or password are not set so bail, or they're set but they don't validate
        if ( (!$email || !$password || !$plans) || !$validate_request->response['payload'] ) {
            return "401";
        }

        if ($validate_request->response['payload']) {

            $user_id = $validate_request->response['payload'];

            // this is a valid login--- so now the question is, are they an active subscriber?
            $plan_id = $this->validateSubscription($user_id, $plans);

            if ($plan_id) {

                // this is a valid subscription so bust out the confetti
                $session = new CASHRequest(null);
                $session->sessionSet("user_id", $user_id);
                $session->sessionSet("plan_id", $plan_id);
                $session->sessionSet("subscription_authenticated", true);

                return $user_id;
            } else {
                return "401";
            }
        }

        // all else fail
        return "401";
    }

    /**
     *
     * Simple lookup to check if a user is an active subscriber
     * @param $user_id
     * @param $plan_id
     * @return bool
     */
    public function validateSubscription($user_id, $plans) {

        if ($member = $this->orm->findWhere(CommerceSubscriptionMember::class, ['user_id'=>$user_id, 'subscription_id'=>$plans])) {
            if (in_array($member->status, ['active', 'comped'])) {
                return $member->subscription_id;
            }
        }

        return false;
    }

    public function getSubscriptionStats($plan_id) {

        if ($result = $this->db->table('commerce_transactions')
            ->select('SUM(commerce_transactions.gross_price) as total_active')
            ->join('commerce_subscriptions_members', function($table)
            {
                $table->on('commerce_subscriptions_members.id', '=', 'commerce_transactions.parent_id');
                $table->on('commerce_transactions.parent', '=', 'sub');
                $table->on('commerce_transactions.status', '=', 'success');
            })
            ->where('commerce_subscriptions_members.status', '=', 'active')
            ->where('commerce_subscriptions_members.subscription_id', '=', $plan_id)->get()) {

            if (is_array($result)) {
                return $result[0]->total_active;
            }

            return $result->total_active;
        }

        return false;
    }

    public function getSubscriberCount($plan_id) {

        if ($result = $this->db->table('commerce_subscriptions_members')
            ->select('COUNT(*) as active_subscribers')
            ->where('status', '=', 'active')
            ->where('subscription_id' , '=', $plan_id)->get()) {
            if (is_array($result)) {
                return $result[0]->active_subscribers;
            } else {
                return $result->active_subscribers;
            }
        }

        return false;
    }

    /**
     * @param $subscriber_user_id
     * @param $subscription_plan
     * @param $data
     * @return mixed
     */
    public function createSubscriptionMember($subscriber_user_id, $plan_id, $data)
    {

        try {
            $member = $this->orm->create(CommerceSubscriptionMember::class, [
                'user_id' => $subscriber_user_id,
                'subscription_id' => $plan_id,
                'status' => 'created',
                'start_date' => strtotime('today'),
                'total_paid_to_date' => 0, // do we need a second field for pledged amount?
                'data' => $data
            ]);
        } catch (Exception $e) {
            return false;
        }

        return $member->id;
    }


    public function initiateSubscription($element_id=false,$price=false,$stripe=false,$origin=false,$email_address=false,$subscription_plan=false,$customer_name=false,$session_id=false,$geo=false, $shipping_info=false, $finalize_url=false) {
        $this->startSession($session_id);
        if (!$element_id) {
            return false;
        } else {

            // do shit

            $user_id = CASHSystem::getUserIdByElement($element_id);

            $default_connections = self::getDefaultConnections($user_id);

            if (is_array($default_connections)) {
                $pp_default = (!empty($default_connections['paypal'])) ? $default_connections['paypal'] : false;
                $pp_micro = (!empty($default_connections['paypal_micro'])) ? $default_connections['paypal_micro'] : false;
                $stripe_default = (!empty($default_connections['stripe'])) ? $default_connections['stripe'] : false;
            } else {
                return false; // no default PP shit set
            }

            $seed_class = '\\CASHMusic\Seeds\\'."StripeSeed";
            if (!class_exists($seed_class)) {
                $this->setErrorMessage("1301 Couldn't find payment type $seed_class.");
                return false;
            }

            // call the payment seed class --- connection id needs to switch later maybe
            $response = $this->createSubscription($element_id,$user_id, $price, $stripe_default, $subscription_plan, $stripe, $email_address, $customer_name, $shipping_info, 1, $finalize_url);

            return $response;

        }

    }


    /**
     * @param $element_id
     * @param $user_id
     * @param $email_address
     * @param $finalize_url
     * @param $email_content
     */
    public static function sendResetValidationEmail($element_id, $user_id, $email_address, $finalize_url, $email_content)
    {
        $reset_key = self::createValidateCustomerURL($email_address);
        $verify_link = $finalize_url . '?key=' . $reset_key . '&address=' .
            urlencode($email_address) .
            '&element_id=' . $element_id;

        $email_content = CASHSystem::renderMustache(
            $email_content, array(
                // array of values to be passed to the mustache template
                'verify_link' => $verify_link
            )
        );

        ###ERROR: error emailing subscriber
        if (empty($email_content)) {
            return false;
        }


        if (!CASHSystem::sendEmail(
            'Welcome to the CASH Music Family',
            $user_id,
            $email_address,
            $email_content,
            'Thank you.'
        )
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param $cash_admin
     * @param $add_request
     */
    public static function createValidateCustomerURL($email_address)
    {

        $reset_key = new CASHRequest(
            array(
                'cash_request_type' => 'system',
                'cash_action' => 'setresetflag',
                'address' => $email_address
            )
        );

        $reset_key = $reset_key->response['payload'];

        if ($reset_key) {
            return $reset_key;
        } else {
            return false;
        }

    }
}