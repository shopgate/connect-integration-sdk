<?php
/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace unit\Service\Authenticator\Exception;

use Shopgate\CloudIntegrationSdk\Service\Authenticator\Exception\Forbidden as ForbiddenException;

class ForbiddenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param \Exception  $expectedResult
     * @param string|null $errorMessage
     *
     * @covers       ForbiddenException::__construct()
     * @dataProvider provideConstructCases
     */
    public function test_construct($expectedResult, $errorMessage)
    {
        $result = $errorMessage === null ? new ForbiddenException() : new ForbiddenException($errorMessage);
        $this->assertEquals($expectedResult->getMessage(), $result->getMessage());
        $this->assertEquals($expectedResult->getCode(), $result->getCode());
    }

    /**
     * @return array
     */
    public function provideConstructCases()
    {
        return array(
            'no message'    => array(
                new \Exception('Forbidden: BasicAuth does not have permissions to access the requested resource.', 403),
                null,
            ),
            'empty message' => array(
                new \Exception('Forbidden: BasicAuth does not have permissions to access the requested resource.', 403),
                '',
            ),
            'some message'  => array(
                new \Exception('Forbidden: some error', 403),
                'some error',
            ),
        );
    }
}