<?php

namespace App\Http\Controllers;

use App\Checkout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function handleWebhook()
    {
        $payload = request()->all();
        $this->connectWebhook($payload);
    }

    public function connectWebhook($payload)
    {
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey(env('STRIPE_SK'));

        // You can find your endpoint's secret in your webhook settings
        $endpoint_secret = env('STRIPE_WEBHOOK_KEY');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        $this->checkEventType($event->type, $event);

        http_response_code(200);
    }

    public function checkEventType($type, $event)
    {
        switch ($type) {
            case 'checkout.session.completed':
                $this->handle_checkout_session($event->data->object);
                break;
            case 'customer.created':
                $this->handle_customer_created($event->data->object);
                break;
            case 'customer.deleted':
                $this->handle_customer_deleted($event->data->object);
                break;
            case 'product.created':
                $this->handle_product_created($event->data->object);
                break;
            case 'plan.created':
                $this->handle_plan_created($event->data->object);
                break;
            case 'plan.deleted':
                $this->handle_plan_deleted($event->data->object);
                break;
            case 'customer.subscription.created':
                $this->handle_customer_subscription_created($event->data->object);
                break;
            case 'customer.subscription.deleted':
                $this->handle_customer_subscription_deleted($event->data->object);
                break;
        }
    }

    public function handle_checkout_session($session)
    {
        die(var_dump($session->id));
    }

    public function handle_customer_created($customer)
    {
        DB::table('checkouts')
            ->where('user_id', 6)
            ->update(['customer_id' => $customer->id, 'status' => 1]);
        die(var_dump("Customer created"));
    }

    public function handle_customer_deleted($customer)
    {
        DB::table('checkouts')
            ->where('customer_id', $customer->id)
            ->update(['status' => 0]);
        die(var_dump("Customer deleted"));

    }

    public function handle_product_created($product)
    {
        DB::table('checkouts')
            ->where('user_id', 6)
            ->update(['product_id' => $product->id]);
        die(var_dump("Product created"));
    }

    public function handle_plan_created($plan)
    {
        DB::table('checkouts')
            ->where('user_id', 6)
            ->update(['plan_id' => $plan->id]);
        die(var_dump("Plan created"));
    }

    public function handle_plan_deleted($plan)
    {
        DB::table('checkouts')
            ->where('plan_id', $plan->id)
            ->update(['status' => 0]);
        die(var_dump("Plan deleted"));

    }

    public function handle_customer_subscription_created($subscription)
    {
        DB::table('checkouts')
            ->where('user_id', 6)
            ->update(['subscription_id' => $subscription->id]);
        die(var_dump("customer has been subscribed to a plan"));
    }

    public function handle_customer_subscription_deleted($subscription)
    {
        DB::table('checkouts')
            ->where('subscription_id', $subscription->id)
            ->update(['status' => 0]);
        die(var_dump("Subscription deleted"));
    }
}
