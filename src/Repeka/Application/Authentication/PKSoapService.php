<?php
namespace Repeka\Application\Authentication;

/**
 * These methods don't actually have to be implemented, they are called magically (__call).
 * @method getClientDataById(string $userId)
 * @method isValidPassword(string $providedByUser, string $cipherText)
 */
class PKSoapService extends \SoapClient {
    public function __construct(?string $wsdl, array $clientOptions) {
        parent::__construct(
            $wsdl,
            array_merge(
                $clientOptions,
                [
                    'trace' => true,
                    'exceptions' => true,
                ]
            )
        );
    }
}
