<?php declare(strict_types=1);

namespace MultiSafepay\WooCommerce\Tests\Fixtures;

/**
 * Class PaymentMethodFixture
 *
 * @package MultiSafepay\WooCommerce\Tests\Fixtures
 */
class PaymentMethodFixture {

    public function get_amex_payment_method_fixture(): array {
        return [
            "additional_data" => [],
            "allowed_amount" => [
                "max" => null,
                "min" => 0
            ],
            "allowed_countries" => [],
            "allowed_currencies" => [
                "EUR",
                "GBP",
                "USD"
            ],
            "apps" => [
                "fastcheckout" => [
                    "is_enabled" => true,
                ],
                "payment_components" => [
                    "has_fields" => true,
                    "is_enabled" => true,
                ],
            ],
            "brands" => [],
            "icon_urls" => [
                "large"  => "https://testmedia.multisafepay.com/img/methods/3x/amex.png",
                "medium" => "https://testmedia.multisafepay.com/img/methods/2x/amex.png",
                "vector" => "https://testmedia.multisafepay.com/img/methods/svg/amex.svg",
            ],
            "id" => "AMEX",
            "name" => "AMEX",
            "preferred_countries" => [],
            "required_customer_data" => [],
            "shopping_cart_required" => false,
            "tokenization" => [
                "is_enabled" => true,
                "models" => [
                    "cardonfile" => true,
                    "subscription" => true,
                    "unscheduled" => true,
                ],
            ],
            "type" => "payment-method"
        ];
    }


    public function get_ideal_payment_method_fixture(): array {
        return [
            "additional_data" => [
                "issuers" => [
                    [
                        "bic" => "ABNANL2A",
                        "code" => "0031",
                        "icon_urls" => [
                            "large" => "https://testmedia.multisafepay.com/img/methods/3x/ideal/0031.png",
                            "medium" => "https://testmedia.multisafepay.com/img/methods/2x/ideal/0031.png",
                            "vector" => "https://testmedia.multisafepay.com/img/methods/svg/ideal/0031.svg"
                        ],
                        "name" => "ABN Amro Bank"
                    ],
                    [
                        "bic" => "ASNBNL21",
                        "code" => "0761",
                        "icon_urls" => [
                            "large" => "https://testmedia.multisafepay.com/img/methods/3x/ideal/0761.png",
                            "medium" => "https://testmedia.multisafepay.com/img/methods/2x/ideal/0761.png",
                            "vector" => "https://testmedia.multisafepay.com/img/methods/svg/ideal/0761.svg"
                        ],
                        "name" => "ASN Bank"
                    ]

                ]
            ],
            "allowed_amount" => [
                "max" => 5000000,
                "min" => 0
            ],
            "allowed_countries" => [],
            "allowed_currencies" => [
                "EUR"
            ],
            "apps" => [
                "fastcheckout" => [
                    "is_enabled" => true,
                ],
                "payment_components" => [
                    "has_fields" => true,
                    "is_enabled" => true,
                ],
            ],
            "brands" => [],
            "icon_urls" => [
                "large"  => "https://testmedia.multisafepay.com/img/methods/3x/ideal.png",
                "medium" => "https://testmedia.multisafepay.com/img/methods/2x/ideal.png",
                "vector" => "https://testmedia.multisafepay.com/img/methods/svg/ideal.svg",
            ],
            "id" => "IDEAL",
            "name" => "IDEAL",
            "preferred_countries" => [],
            "required_customer_data" => [],
            "shopping_cart_required" => false,
            "tokenization" => [
                "is_enabled" => true,
                "models" => [
                    "cardonfile" => true,
                    "subscription" => true,
                    "unscheduled" => true,
                ],
            ],
            "type" => "payment-method"
        ];
    }

    public function get_payment_methods_example_response_from_api() {
        return [
            $this->get_amex_payment_method_fixture(),
            $this->get_ideal_payment_method_fixture()
        ];
    }
}
