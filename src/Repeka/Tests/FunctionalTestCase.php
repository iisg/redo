<?php
namespace Repeka\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class FunctionalTestCase extends WebTestCase {
    // https://goo.gl/7Hl6Os simplified
    // Cannot be type-hinted because it receives and returns various data through recursion
    protected static function objectToArray($obj) {
        if (is_object($obj)) {
            $obj = get_object_vars($obj);
        }
        return (is_array($obj))
            ? array_map(__CLASS__ . '::' . __FUNCTION__, $obj)
            : $obj;
    }

    protected static function getStatusLine(Response $response): string {
        $fullResponse = (string)$response;
        return trim(strtok($fullResponse, "\n"));
    }

    private static function convertNumericArrayKeysToStrings($input) {
        if (!is_array($input)) {
            return $input;
        }
        $result = [];
        foreach ($input as $key => $value) {
            // only integers and strings are allowed as keys, so this is safe:
            $result[(string)$key] = self::convertNumericArrayKeysToStrings($value);
        }
        return $result;
    }

    /**
     * Asserts that string representing JSON object (eg. server's response content) is alike provided array. Ignores order of items in JSON
     * lists and handles stdclass/array inequality.
     * @param $expectedArray
     * @param $actualJsonString
     * @param string $message
     */
    protected static function assertJsonStringSimilarToArray($expectedArray, $actualJsonString, $message = '') {
        static::assertJson($actualJsonString);
        $actual = self::objectToArray(json_decode($actualJsonString));
        // uniform array keys
        $actual = self::convertNumericArrayKeysToStrings($actual);
        $expectedArray = self::convertNumericArrayKeysToStrings($expectedArray);
        // sort arrays because order doesn't matter
        array_multisort($expectedArray);
        array_multisort($actual);
        static::assertEquals($expectedArray, $actual, $message);
    }

    /**
     * Asserts that HTTP response's status code is equal to expected or belongs to expected family.
     * @param $expectedStatus Integer representing code (eg. 200, 404) or string representing code family (eg. '2xx', '4xx')
     * @param Response $clientResponse
     */
    protected static function assertStatusCode($expectedStatus, Response $clientResponse) {
        $actualStatus = $clientResponse->getStatusCode();
        $fullStatusLine = self::getStatusLine($clientResponse);
        $message = "Response status $actualStatus isn't %s: $fullStatusLine. Response content: \n" . $clientResponse->getContent();
        if (is_int($expectedStatus)) {
            static::assertEquals($expectedStatus, $actualStatus, sprintf($message, $expectedStatus));
        } elseif (preg_match('/^[1-5]xx$/i', $expectedStatus)) {
            $firstDigit = intval($expectedStatus[0]);
            static::assertEquals($firstDigit, floor($actualStatus / 100), sprintf($message, $expectedStatus));
        } elseif ($expectedStatus === false) {
            static::assertNotEquals('2', floor($actualStatus / 100), sprintf($message, 'non-successful'));
        }
    }

    protected static function assertArrayHasAllValues($expectedValues, $actualArray) {
        foreach ($expectedValues as $value) {
            static::assertContains($value, $actualArray);
        }
    }
}
