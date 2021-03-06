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

namespace Shopgate\ConnectSdk\Dto\Location\Location;

use Shopgate\ConnectSdk\Dto\Location\Location;

/**
 * @inheritDoc
 *
 * @codeCoverageIgnore
 */
class Get extends Location
{
    /**
     * @var array
     */
    protected $schema = [
        'type' => 'object',
        'properties' => [
            'type' => ['$ref' => Dto\Type::class, 'skipValidation' => true],
            'operationHours' => ['$ref' => Dto\OperationHours::class, 'skipValidation' => true],
            'details' => ['$ref' => Dto\Details::class, 'skipValidation' => true],
            'addresses' => [
                'type' => 'array',
                'items' => ['$ref' => Dto\Address::class, 'skipValidation' => true]
            ],
            'inventory' => ['$ref' => Dto\Inventory::class, 'skipValidation' => true],
            'settings' => ['$ref' => Dto\Settings::class, 'skipValidation' => true],
        ],
        'additionalProperties' => true
    ];
}
