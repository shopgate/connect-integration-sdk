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

namespace Shopgate\ConnectSdk\Services\Events\DTO\Payload;

use Exception;
use Shopgate\ConnectSdk\Services\Events\Connector\DTO\Payload;

/**
 * @property-read Payload\Catalog catalog
 */
class Factory
{
    /**
     * @var Payload\Base
     */
    protected $dto;

    /** @noinspection MagicMethodsValidityInspection */
    /**
     * For redirecting calls like $payloadFactory->catalog->... to the right connector, e.g Connector\Payload\Catalog
     *
     * @param string $name
     *
     * @return Payload\Base
     * @throws Exception
     */
    public function __get($name)
    {
        if (isset($this->dto[$name])) {
            return $this->dto[$name];
        }

        return $this->dto[$name] = $this->instantiateClass($name);
    }

    /**
     * A factory for connector classes
     *
     * @param string $name
     *
     * @return Payload\Base
     * @throws Exception
     */
    private function instantiateClass($name)
    {
        $class = 'Shopgate\ConnectSdk\Services\Events\Connector\DTO\Payload\\' . ucfirst($name);
        if (class_exists($class)) {
            return new $class();
        }
        //todo-sg: custom exception for Connectors
        throw new Exception('Connector does not exist');
    }
}