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

namespace Shopgate\ConnectSdk\Services\Events\Entities\Catalog\Category;

use Dto\Exceptions\InvalidDataTypeException;
use Shopgate\ConnectSdk\Services\Events\DTO\Async\Factory as EventFactory;
use Shopgate\ConnectSdk\Services\Events\Entities;

class Async implements Entities\EntityInterface
{
    use Entities\EntityTrait;

    const ENTITY = 'category';

    /**
     * @inheritDoc
     *
     * @used-by \Shopgate\ConnectSdk\Services\Events\Connector\Entities\Catalog::__call()
     * @throws InvalidDataTypeException
     */
    public function update($entityId, $data = [], $meta = [])
    {
        $updateEvent = new EventFactory();
        $this->addEvent($updateEvent, $entityId, $data);

        //todo-sg: mark an exception thrown here possibly, implementer needs to handle
        return $this->client->request(
            'post',
            'events',
            ['json' => $updateEvent->getRequest()->toArray(), 'query' => $meta]
        );
    }
}
