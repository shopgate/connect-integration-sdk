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

namespace Shopgate\ConnectSdk\Dto\Catalog\Reservation;

use Shopgate\ConnectSdk\Dto\Catalog\Reservation as ReservationBase;

/**
 * @inheritdoc
 */
class Create extends ReservationBase
{
    /**
     * @var array
     */
    protected $schema = [
        'type'                 => 'object',
        'reservations'         => [
            'productCode'            => ['type' => 'string'],
            'locationCode'           => ['type' => 'string'],
            'sku'                    => ['type' => 'string'],
            'salesOrderLineItemCode' => ['type' => 'string'],
            'salesOrderNumber'       => ['type' => 'string'],
            'fulfillmentOrderNumber' => ['type' => 'string'],
            'bin'                    => ['type' => 'string'],
            'binLocation'            => ['type' => 'string'],
            'quantity'               => ['type' => 'int'],
        ],
        'additionalProperties' => true,
    ];
}
