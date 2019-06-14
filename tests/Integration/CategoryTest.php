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

namespace Shopgate\ConnectSdk\Tests\Integration;

use Psr\Http\Message\ResponseInterface;
use Shopgate\ConnectSdk\Dto\Catalog\Category;
use Shopgate\ConnectSdk\Dto\Catalog\Product\Dto\Name;
use Shopgate\ConnectSdk\Exception\Exception;
use Shopgate\ConnectSdk\Exception\RequestException;

class CategoryTest extends ShopgateSdkTest
{
    const PARENT_CATEGORY_CODE = 'parent-integration-test';
    const CATEGORY_CODE = 'integration-test';

    private $cleanUpCategoryCodes = [];

    /**
     * Runs before every test
     */
    public function setUp()
    {
        parent::setUp();

        $this->cleanUpCategoryCodes = [];
    }

    /**
     * @throws Exception
     */
    public function tearDown()
    {
        parent::tearDown();

        foreach ($this->cleanUpCategoryCodes as $categoryCode) {
            $this->sdk->getCatalogService()->deleteCategory(
                $categoryCode, [
                    'requestType' => 'direct'
                ]
            );
        }
    }

    /**
     * @throws Exception
     */
    public function testCreateCategoryDirect()
    {
        // Arrange
        $sampleCategories = $this->provideSampleCategories();
        $sampleCategoryCodes = $this->getCategoryCodes($sampleCategories);

        // Act
        $this->createCategories($sampleCategories, [
            'requestType' => 'direct'
        ]);

        // CleanUp
        $this->cleanUpCategoryCodes = array_merge($this->cleanUpCategoryCodes, $sampleCategoryCodes);

        // Assert
        $categories = $this->getCategories($sampleCategoryCodes);
        /** @noinspection PhpParamsInspection */
        $this->assertCount(2, $categories->getCategories());
    }

    /**
     * @throws Exception
     */
    public function testUpdateCategoryDirect()
    {
        // Arrange
        $this->sdk->getCatalogService()->addCategories(
            [
                $this->provideSampleCreateCategory(
                    self::CATEGORY_CODE,
                    'Integration Test Category 1',
                    1
                )
            ],
            ['requestType' => 'direct']
        );
        $newName = 'Renamed Product (Direct)';
        $category = new Category\Update(['name' => new Name(['en-us' => $newName])]);

        // Act
        $this->sdk->getCatalogService()->updateCategory(self::CATEGORY_CODE, $category, [
            'requestType' => 'direct'
        ]);

        // CleanUp
        $this->cleanUpCategoryCodes[] = self::CATEGORY_CODE;

        // Assert
        $categories = $this->getCategories([self::CATEGORY_CODE]);
        $updatedCategory = $categories->getCategories()[0];
        $this->assertEquals($newName, $updatedCategory->getName());
    }

    /**
     * @param array $updateCategoryData
     * @param string $expectedValue
     * @throws Exception
     *
     * @dataProvider provideUpdateCategoryData
     */
    public function testUpdateCategoryPropertyDirect(array $updateCategoryData, $expectedValue)
    {
        // Arrange
        $this->sdk->getCatalogService()->addCategories(
            [
                $this->provideSampleCreateCategory(
                    self::PARENT_CATEGORY_CODE,
                    'Parent Integration Test Category',
                    1,
                    'http://www.google.de/parent.png',
                    'https://www.google.de/parent',
                    'test parent description'
                ),
                $this->provideSampleCreateCategory(
                    self::CATEGORY_CODE,
                    'Integration Test Category 1',
                    1,
                    'http://www.google.de/image.png',
                    'https://www.google.de',
                    'test description'
                )
            ],
            ['requestType' => 'direct']
        );
        $category = new Category\Update($updateCategoryData);

        // Act
        $this->sdk->getCatalogService()->updateCategory(self::CATEGORY_CODE, $category, [
            'requestType' => 'direct'
        ]);

        // CleanUp
        $this->cleanUpCategoryCodes[] = self::CATEGORY_CODE;
        $this->cleanUpCategoryCodes[] = self::PARENT_CATEGORY_CODE;

        // Assert
        $categories = $this->getCategories([self::CATEGORY_CODE], ['getOriginalImageUrls' => 'true']);
        $updatedCategory = $categories->getCategories()[0];
        $updatedKey = array_keys($updateCategoryData)[0];
        $this->assertEquals($expectedValue, $updatedCategory->get($updatedKey));
    }

    /**
     * @return array
     */
    public function provideUpdateCategoryData()
    {
        return [
            'name' => [
                'updateCategoryData' => [
                    'name' => new Name(['en-us' => 'Updated Name']),
                ],
                'expectedValue' => 'Updated Name'
            ],
            'description' => [
                'updateCategoryData' => [
                    'description' => new Category\Dto\Description(['en-us' => 'Updated Description']),
                ],
                'expectedValue' => 'Updated Description'
            ],
            'image' => [
                'updateCategoryData' => [
                    'image' => 'http://updated.com/image.png',
                ],
                'expectedValue' => 'http://updated.com/image.png'
            ],
            'url' => [
                'updateCategoryData' => [
                    'url' => 'http://updated.url.com',
                ],
                'expectedValue' => 'http://updated.url.com'
            ],
            'parentCategoryCode' => [
                'updateCategoryData' => [
                    'parentCategoryCode' => self::PARENT_CATEGORY_CODE,
                ],
                'expectedValue' => self::PARENT_CATEGORY_CODE
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function testDeleteCategoryDirect()
    {
        // Arrange
        $sampleCategories = $this->provideSampleCategories();
        $this->sdk->getCatalogService()->addCategories($sampleCategories, ['requestType' => 'direct']);

        // Act
        foreach ($this->getCategoryCodes($sampleCategories) as $categoryCode) {
            $this->sdk->getCatalogService()->deleteCategory($categoryCode, [
                'requestType' => 'direct'
            ]);
        }

        // Assert
        $categories = $this->getCategories($this->getCategoryCodes($sampleCategories));
        /** @noinspection PhpParamsInspection */
        $this->assertCount(0, $categories->getCategories());
    }

    /**
     * @throws Exception
     */
    public function testGetCategories()
    {
        // Arrange
        $sampleCategories = $this->provideSampleCategories();
        $this->sdk->getCatalogService()->addCategories($sampleCategories, ['requestType' => 'direct']);

        // Act
        $categories = $this->sdk->getCatalogService()->getCategories();

        // CleanUp
        $this->cleanUpCategoryCodes = array_merge($this->cleanUpCategoryCodes,
            $this->getCategoryCodes($sampleCategories)
        );

        // Assert
        /** @noinspection PhpParamsInspection */
        $this->assertCount(2, $categories->getCategories());
    }

    /**
     * @param array $categoryData
     * @param RequestException $expectedException
     *
     * @throws Exception
     *
     * @dataProvider provideCreateCategory_MissingRequiredFields
     */
    public function testCreateCategoryDirect_MissingRequiredFields(array $categoryData, $expectedException)
    {
        // Arrange
        $category = new Category\Create($categoryData);

        // Act
        try {
            $this->createCategories([$category], [
                'requestType' => 'direct'
            ]);
        } catch (RequestException $exception) {
            // Assert
            $this->assertInstanceOf(get_class($expectedException), $exception);
            $this->assertEquals($expectedException->getStatusCode(), $exception->getStatusCode());

            return;
        }

        $this->fail('Expected ' . get_class($expectedException) . ' but wasn\'t thrown');
    }

    /**
     * @return array
     */
    public function provideCreateCategory_MissingRequiredFields()
    {
        return [
            'missing name' => [
                'categoryData' => [
                    'code' => 'category-test-code',
                    'sequenceId' => 1006
                ],
                'expectedException' => new RequestException(400)
            ],
            'missing code' => [
                'categoryData' => [
                    'name' => 'Test Category',
                    'sequenceId' => 1006
                ],
                'expectedException' => new RequestException(400)
            ],
            'missing sequenceId' => [
                'categoryData' => [
                    'name' => 'Test Category',
                    'code' => 'category-test-code',
                ],
                'expectedException' => new RequestException(400)
            ],
        ];
    }

    /**
     * @param array $categoryData
     * @param RequestException $expectedException
     *
     * @throws Exception
     *
     * @dataProvider provideCreateCategory_InvalidDataTypes
     */
    public function testCreateCategoryDirect_InvalidDataTypes($categoryData, $expectedException)
    {
        // Arrange
        $category = new Category\Create($categoryData);

        // Act
        try {
            $this->createCategories([$category], [
                'requestType' => 'direct'
            ]);
        } catch (RequestException $exception) {
            // Assert
            $this->assertInstanceOf(get_class($expectedException), $exception);
            $this->assertEquals($expectedException->getStatusCode(), $exception->getStatusCode());

            return;
        }

        $this->fail('Expected ' . get_class($expectedException) . ' but wasn\'t thrown');
    }

    /**
     * @return array
     */
    public function provideCreateCategory_InvalidDataTypes()
    {
        return [
            'wrong name data type' => [
                'categoryData' => [
                    'name' => 12345,
                    'code' => 'category-test-code',
                    'sequenceId' => 1006
                ],
                'expectedException' => new RequestException(400)
            ],
            'wrong code data type' => [
                'categoryData' => [
                    'name' => 'Test Category',
                    'code' => 123456,
                    'sequenceId' => 1006
                ],
                'expectedException' => new RequestException(400)
            ],
            'wrong sequenceId data type' => [
                'categoryData' => [
                    'name' => 'Test Category',
                    'code' => 'category-test-code',
                    'sequenceId' => '1006'
                ],
                'expectedException' => new RequestException(400)
            ],
            'wrong parentCategoryCode data type' => [
                'categoryData' => [
                    'name' => 'Test Category',
                    'code' => 'category-test-code',
                    'sequenceId' => 1006,
                    'parentCategoryCode' => 12345
                ],
                'expectedException' => new RequestException(400)
            ],
            'wrong image data type' => [
                'categoryData' => [
                    'name' => 'Test Category',
                    'code' => 'category-test-code',
                    'sequenceId' => 1006,
                    'image' => 12345
                ],
                'expectedException' => new RequestException(400)
            ],
            'wrong url type' => [
                'categoryData' => [
                    'name' => 'Test Category',
                    'code' => 'category-test-code',
                    'sequenceId' => 1006,
                    'url' => 123456
                ],
                'expectedException' => new RequestException(400)
            ],
            'wrong description type' => [
                'categoryData' => [
                    'name' => 'Test Category',
                    'code' => 'category-test-code',
                    'sequenceId' => 1006,
                    'description' => new Category\Dto\Description(['en-US' => 12345])
                ],
                'expectedException' => new RequestException(400)
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function testUpdateCategory_WithoutAnyDataGiven()
    {
        // Arrange
        $categoryCode = 'example-code';
        $existingCategory = $this->provideSampleCreateCategory(
            $categoryCode,
            'test category',
            'http://www.google.e/image.png',
            'http://www.google.de',
            'test description',
            '12345'
        );
        $this->sdk->getCatalogService()->addCategories([$existingCategory], [
            'requestType' => 'direct'
        ]);
        $updateCategory = new Category\Update();

        // Act
        $response = $this->sdk->getCatalogService()->updateCategory($categoryCode, $updateCategory, [
            'requestType' => 'direct'
        ]);

        // Assert
        $this->assertEquals(204, $response->getStatusCode());

        // Cleanup
        $this->cleanUpCategoryCodes[] = $categoryCode;
    }

    /**
     * @throws Exception
     */
    public function testUpdateCategory_NonExistingCategory()
    {
        // Arrange
        $nonExistentCategoryCode = 'non-existent';
        $category = $this->provideSampleUpdateCategory('test non existent category');

        // Act
        try {
            $this->sdk->getCatalogService()->updateCategory($nonExistentCategoryCode, $category, [
                'requestType' => 'direct'
            ]);
        } catch (RequestException $exception) {
            // Assert
            $this->assertEquals(404, $exception->getStatusCode());

            return;
        }

        $this->fail('Expected RequestException but wasn\'t thrown');
    }

    // TODO: It seems only one category is created in the service. Cause of this bug:
    // https://gitlab.localdev.cc/omnichannel/services/worker/blob/v1.0.0-beta.10c/app/EventController.js#L37
    // the return will interrupt the execution of following events
    // will be fixed once we can use something later than omni-worker: v1.0.0-beta.10c
    /**
     * @throws Exception
     */
    public function testCreateCategoryEvent()
    {
        $this->markTestSkipped('Skipped due to bug in worker service');

        // Arrange
        $sampleCategories = $this->provideSampleCategories();
        $sampleCategoryCodes = $this->getCategoryCodes($sampleCategories);

        // Act
        $response = $this->createCategories($sampleCategories);
        sleep(self::SLEEP_TIME_AFTER_EVENT);

        // CleanUp
        $this->cleanUpCategoryCodes = array_merge($this->cleanUpCategoryCodes, $sampleCategoryCodes);

        // Assert
        $categories = $this->getCategories($sampleCategoryCodes);
        $this->assertEquals(202, $response->getStatusCode());
        /** @noinspection PhpParamsInspection */
        $this->assertCount(2, $categories->getCategories());
    }

    /**
     * @throws Exception
     */
    public function testUpdateCategoryEvent()
    {
        // Arrange
        $this->sdk->getCatalogService()->addCategories(
            [
                $this->provideSampleCreateCategory(self::CATEGORY_CODE, 'Integration Test Category 1', 1)
            ],
            ['requestType' => 'direct']
        );
        $newName = 'Renamed Product (Event)';
        $updatedCategory = new Category\Update(['name' => new Name(['en-us' => $newName])]);

        // Act
        $response = $this->sdk->getCatalogService()->updateCategory(self::CATEGORY_CODE, $updatedCategory);
        sleep(self::SLEEP_TIME_AFTER_EVENT);

        // CleanUp
        $this->cleanUpCategoryCodes[] = self::CATEGORY_CODE;

        // Assert
        $categories = $this->getCategories([self::CATEGORY_CODE]);
        $updatedCategory = $categories->getCategories()[0];
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertEquals($newName, $updatedCategory->getName());
    }

    /**
     * @throws Exception
     */
    public function testDeleteCategoryEvent()
    {
        // Arrange
        $sampleCategories = $this->provideSampleCategories();
        $this->sdk->getCatalogService()->addCategories($sampleCategories, ['requestType' => 'direct']);
        $responses = [];

        // Act
        foreach ($this->getCategoryCodes($sampleCategories) as $categoryCode) {
            $responses[] = $this->sdk->getCatalogService()->deleteCategory($categoryCode);
            // should be moved out of loop once the omni-worker service is released with a version higher worker:v1.0.0-beta.10d
            sleep(self::SLEEP_TIME_AFTER_EVENT);
        }

        // Assert
        $categories = $this->getCategories($this->getCategoryCodes($sampleCategories));
        /** @noinspection PhpParamsInspection */
        $this->assertCount(0, $categories->getCategories());

        foreach ($responses as $response) {
            $this->assertEquals(202, $response->getStatusCode());
        }
    }

    /**
     * @param array $categoryData
     * @param string $expectedException
     *
     * @throws Exception
     *
     * @dataProvider provideCreateCategory_MissingRequiredFields
     * TODO: Currently there is no validation for events! Waiting for the implementation in service
     */
//    public function testCreateCategoryEvent_MissingRequiredFields(array $categoryData, $expectedException)
//    {
//        // Arrange
//        $category = new Category\Create($categoryData);
//
//        // Assert
//        $this->expectException($expectedException);
//
//        // Act
//        $this->createCategories([$category], [
//            'requestType' => 'direct'
//        ]);
//    }

    /**
     * @param array $categoryData
     * @param string $expectedException
     *
     * @throws Exception
     *
     * @dataProvider provideCreateCategory_InvalidDataTypes
     * TODO: Currently there is no validation for events! Waiting for the implementation in service
     */
//    public function testCreateCategoryEvent_InvalidDataTypes($categoryData, $expectedException)
//    {
//        // Arrange
//        $category = new Category\Create($categoryData);
//
//        // Assert
//        $this->expectException($expectedException);
//
//        // Act
//        $this->createCategories([$category]);
//    }

    /**
     * @param array $categoryCodes
     * @param array $meta
     *
     * @return Category\GetList
     * @throws Exception
     *
     */
    private function getCategories($categoryCodes = [], $meta = [])
    {
        return $this->sdk->getCatalogService()->getCategories(array_merge(['filters' => ['code' => ['$in' => $categoryCodes]]],
            $meta));
    }

    /**
     * @param Category\Create[] $sampleCategories
     * @param array $meta
     *
     * @return ResponseInterface
     * @throws RequestException
     * @throws Exception
     *
     */
    private function createCategories(array $sampleCategories, array $meta = [])
    {
        return $this->sdk->getCatalogService()->addCategories($sampleCategories, $meta);
    }

    /**
     * @return Category\Create[]
     */
    private function provideSampleCategories()
    {
        return [
            $this->provideSampleCreateCategory(self::CATEGORY_CODE, 'Integration Test Category 1', 1),
            $this->provideSampleCreateCategory(self::CATEGORY_CODE . '_2', 'Integration Test Category 2', 2)
        ];
    }

    /**
     * @param string $name
     * @param string $image
     * @param string $url
     * @param string $description
     * @param string $parentCategoryCode
     * @return Category\Update
     */
    private function provideSampleUpdateCategory(
        $name = null,
        $image = null,
        $url = null,
        $description = null,
        $parentCategoryCode = null

    ) {
        $category = new Category\Update();

        if ($name) {
            $translatedName = new Category\Dto\Name(['en-us' => $name]);
            $category->setName($translatedName);
        }
        if ($url) {
            $category->setUrl($url);
        }
        if ($description) {
            $translatedDescription = new Category\Dto\Description($description);
            $category->setDescription($translatedDescription);
        }
        if ($image) {
            $category->setImage($image);
        }
        if ($parentCategoryCode) {
            $category->setParentCategoryCode($parentCategoryCode);
        }

        return $category;
    }

    /**
     * @param string $code
     * @param string $name
     * @param int $sequenceId
     * @param string $image
     * @param string $url
     * @param string $description
     * @param string $parentCategoryCode
     * @return Category\Create
     */
    private function provideSampleCreateCategory(
        $code,
        $name,
        $sequenceId,
        $image = null,
        $url = null,
        $description = null,
        $parentCategoryCode = null

    ) {
        $category = new Category\Create();
        $category->setCode($code)
            ->setName(new Category\Dto\Name(['en-us' => $name]))
            ->setSequenceId($sequenceId);
        if ($url) {
            $category->setUrl($url);
        }
        if ($description) {
            $translatedDescription = new Category\Dto\Description(['en-us' => $description]);
            $category->setDescription($translatedDescription);
        }
        if ($image) {
            $category->setImage($image);
        }
        if ($parentCategoryCode) {
            $category->setParentCategoryCode($parentCategoryCode);
        }

        return $category;
    }

    /**
     * @param Category\Create[] $categories
     *
     * @return string[]
     */
    private function getCategoryCodes($categories)
    {
        $categoryCodes = [];
        foreach ($categories as $category) {
            $categoryCodes[] = $category->code;
        }

        return $categoryCodes;
    }
}
