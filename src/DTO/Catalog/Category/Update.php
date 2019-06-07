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

namespace Shopgate\ConnectSdk\DTO\Catalog;

use Shopgate\ConnectSdk\DTO\Base as DTOBase;
use Shopgate\ConnectSdk\DTO\Catalog\Category\Description;
use Shopgate\ConnectSdk\DTO\Catalog\Category\Name;

/**
 * Default class that handles validation for category Update payloads.
 *
 * @method Update setImage(string $image)
 * @method Update setName(Name $name)
 * @method Update setParentCategoryCode(string $parentCategoryCode)
 * @method Update setUrl(string $url)
 * @method Update setDescription(Description $description)
 */
class Update extends DTOBase
{
    /**
     * @var array
     * @codeCoverageIgnore
     */
    protected $schema = [
        'type'                 => 'object',
        'properties'           => [
            'image'              => ['type' => 'string'],
            'name'               => ['type' => 'object'],
            'parentCategoryCode' => ['type' => 'string'],
            'url'                => ['type' => 'string'],
            'description'        => ['type' => 'object'],
        ],
        'additionalProperties' => true
    ];
}