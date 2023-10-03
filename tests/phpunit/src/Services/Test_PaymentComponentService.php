<?php declare(strict_types=1);

use MultiSafepay\WooCommerce\Services\PaymentComponentService;
use MultiSafepay\Api\PaymentMethods\PaymentMethod;
use MultiSafepay\WooCommerce\PaymentMethods\Base\BasePaymentMethod;
use MultiSafepay\WooCommerce\Tests\Fixtures\PaymentMethodFixture;

class Test_PaymentComponentService extends WP_UnitTestCase {

    /**
     * @var PaymentComponentService
     */
    public $payment_component_service;

    /**
     * @var PaymentMethod
     */
    public $payment_method;

    /**
     * @var BasePaymentMethod;
     */
    public $woocommerce_payment_gateway;


    public function set_up() {
        $this->payment_method = new PaymentMethod( ( new PaymentMethodFixture() )->get_amex_payment_method_fixture() );
        $this->woocommerce_payment_gateway = new BasePaymentMethod( $this->payment_method );
        $this->payment_component_service = New PaymentComponentService();

        $sdk_service = $this->getMockBuilder('SdkService')
            ->disableOriginalConstructor()
            ->setMethods(['get_test_mode'])
            ->getMock();

        $api_token_service = $this->getMockBuilder('ApiTokenService')
            ->disableOriginalConstructor()
            ->setMethods(['get_api_token'])
            ->getMock();

        $sdk_service->method('get_test_mode')->willReturn(true);
        $api_token_service->method('get_api_token')->willReturn('fake-api-token');

        $this->payment_component_service->sdk_service = $sdk_service;
        $this->payment_component_service->api_token_service = $api_token_service;

    }

    public function test_payment_component_service() {
        $payment_component_arguments = $this->payment_component_service->get_payment_component_arguments( $this->woocommerce_payment_gateway );
        $this->assertIsArray( $payment_component_arguments );
        $this->assertArrayHasKey( 'debug', $payment_component_arguments );
        $this->assertArrayHasKey( 'env', $payment_component_arguments );
        $this->assertArrayHasKey( 'api_token', $payment_component_arguments );
        $this->assertArrayHasKey( 'orderData', $payment_component_arguments );
        $this->assertIsArray( $payment_component_arguments['orderData'] );
        $this->assertArrayHasKey( 'currency', $payment_component_arguments['orderData'] );
        $this->assertArrayHasKey( 'amount', $payment_component_arguments['orderData'] );
        $this->assertArrayHasKey( 'customer', $payment_component_arguments['orderData'] );
        $this->assertIsArray( $payment_component_arguments['orderData']['customer'] );
        $this->assertArrayHasKey( 'locale', $payment_component_arguments['orderData']['customer'] );
        $this->assertArrayHasKey( 'country', $payment_component_arguments['orderData']['customer'] );
        $this->assertArrayHasKey( 'template', $payment_component_arguments['orderData'] );
        $this->assertIsArray( $payment_component_arguments['orderData']['template'] );
        $this->assertArrayHasKey( 'settings', $payment_component_arguments['orderData']['template'] );
        $this->assertIsArray( $payment_component_arguments['orderData']['template']['settings'] );
        $this->assertArrayHasKey( 'embed_mode', $payment_component_arguments['orderData']['template']['settings'] );
        $this->assertIsArray( $payment_component_arguments['orderData']['payment_options'] );
        $this->assertArrayHasKey( 'settings', $payment_component_arguments['orderData']['payment_options'] );
        $this->assertIsArray( $payment_component_arguments['orderData']['payment_options']['settings'] );
        $this->assertArrayHasKey( 'connect', $payment_component_arguments['orderData']['payment_options']['settings'] );
        $this->assertIsArray( $payment_component_arguments['orderData']['payment_options']['settings']['connect'] );
        $this->assertArrayHasKey( 'issuers_display_mode', $payment_component_arguments['orderData']['payment_options']['settings']['connect'] );
        $this->assertNotEmpty( $payment_component_arguments['gateway'] );
    }
}
