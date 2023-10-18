1. we need to run command composer require stripe/stripe-php
2. When you create an account switch to developers mode in your dashboard. 
In developers mode, you will see a publishable key(PK) and a secret key(SK). Add both keys to .env files to as
STRIPE_PK=pk_test_bgmxxxxxxxxxxxx
STRIPE_SK=sk_test_TWzxxxxxxxx
STRIPE_WEBHOOK_KEY=whsec_umbxxxxx
3. Download ngrok https://ngrok.com/ and follow the documents to create an https link.

ngrok http 8000
Forwarding https://38cf87be.ngrok.io -> http://localhost:8000

4. Under Developers menu we can see Webhooks and inside it, we can see the “Add endpoint” button. Click the button and add the https URL generated using ngrok in “Endpoint URL” i.e https://38cf87be.ngrok.io/stripe/webhook. (append stripe/webhook). 
Choose the version you need and select the events as per your need. I have here selected a few events which you can see below:

checkout.session.completed
customer.created
customer.deleted
product.created
plan.created
plan.deleted
customer.subscription.created
customer.subscription.deleted

5. Test it by using “Send test webhook”, but before that let's see what we need to do in server-side.
First, create a database table with your required columns, For Eg:
public function up()
{
    Schema::create('checkouts', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedInteger('user_id');
        $table->foreign('user_id')->references('id')->on('users');
        $table->string('customer_id')->nullable();
        $table->string('subscription_id')->nullable();
        $table->string('product_id')->nullable();
        $table->string('plan_id')->nullable();
        $table->boolean('status')->nullable();
        $table->timestamps();
    });
}

In web.php, please add the following code:
Route::post('stripe/webhook','WebhookController@handleWebhook');

In VerifyCsrfToken.php, add

protected $except = [
    'stripe/*',
];

Create WebhookController and a handleWebhook function inside it.

6. Now we have completed our setup and server-side code, let’s focus on the front-end/ client-side. On the client-side, we are just displaying “checkout” button which will navigate to stripe checkout page. Let's get back to work now. In the stripe dashboard when you create a product and plan, you will see “Use with checkout” button. Click the button, you can see javascript related to that specific plan.


