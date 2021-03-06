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

namespace Shopgate\ConnectSdk\Dto\Order\Order;

use Shopgate\ConnectSdk\Dto\Order\Order;

/**
 * @inheritDoc
 * @codeCoverageIgnore
 */
class Get extends Order
{
    /**
     * @var array
     */
    protected $schema = [
        'type'                 => 'object',
        'properties'           => [
            'specialInstructions' => ['$ref' => Dto\SpecialInstructions::class, 'skipValidation' => true],
            'data'                => ['$ref' => Dto\Data::class, 'skipValidation' => true],
            'addressSequences'    => [
                'type'  => 'array',
                'items' => ['$ref' => Dto\Address::class, 'skipValidation' => true]
            ],
            'fulfillmentGroups'   => [
                'type'  => 'array',
                'items' => ['$ref' => Dto\FulfillmentGroup::class, 'skipValidation' => true]
            ],
            'lineItems'           => [
                'type'  => 'array',
                'items' => ['$ref' => Dto\LineItem::class, 'skipValidation' => true]
            ]
        ],
        'additionalProperties' => true
    ];
}
