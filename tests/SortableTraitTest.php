<?php

require_once 'stubs/SortableEntity.php';
require_once 'SortableTestBase.php';

class SortableTraitTest extends SortableTestBase
{
    public function setUp()
    {
        parent::setUp();

        // fix for "Eloquent model events are not triggered when testing" https://github.com/laravel/framework/issues/1181
        SortableEntity::boot();
    }

    public function testPositionOnCreate()
    {
        $entity = new SortableEntity();
        $entity->save();
        $this->assertEquals(1, $entity->position);

        $entity2 = new SortableEntity();
        $entity2->save();
        $this->assertEquals(2, $entity2->position);
    }

    public function testPosition()
    {

        /** @var SortableEntity[] $entities */
        $entities = [];
        for ($i = 1; $i <= 30; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
            $this->assertEquals($i, $entities[$i]->id);
            $this->assertEquals($i, $entities[$i]->position);
        }
    }

    /**
     * @param
     * @param
     * @param
     * @dataProvider moveWhenMovedEntityComesBeforeRelativeEntityProvider
     */
    public function testMoveAfterWhenMovedEntityComesBeforeRelativeEntity($entityId, $relativeEntityId, $countTotal)
    {

        // interavls: [1 .. $entityId - 1], [$entityId], [$entityId + 1 .. $relativeEntityId], [$relativeEntityId .. $countTotal]

        /** @var SortableEntity[] $entities */
        $entities = [];
        for ($i = 1; $i <= $countTotal; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
        }

        $moveEntity = $entities[$entityId];
        $relyEntity = $entities[$relativeEntityId];

        $moveEntity->moveAfter($relyEntity);

        $this->assertEquals($relativeEntityId, $moveEntity->position);
        $this->assertEquals($relativeEntityId - 1, $relyEntity->position);

        // check [1 .. $entityId - 1] entities
        for ($id = 1; $id < $entityId; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id, $entity->position);
        }

        // check $entityId entity
        $entity = SortableEntity::find($entityId);
        $this->assertEquals($relativeEntityId, $entity->position);

        // check  [$entityId + 1 .. $relativeEntityId] entities
        for ($id = $entityId + 1; $id <= $relativeEntityId; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id - 1, $entity->position);
        }

        // check  [$relativeEntityId + 1 .. $countTotal] entities
        for ($id = $relativeEntityId + 1; $id <= $countTotal; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id, $entity->position);
        }
    }

    /**
     * @param
     * @param
     * @param
     * @dataProvider moveWhenMovedEntityComesAfterRelativeEntityProvider
     */
    public function testMoveAfterWhenMovedEntityComesAfterRelativeEntity($entityId, $relativeEntityId, $countTotal)
    {
        // interavls: [1 .. $relativeEntityId], , [$relativeEntityId + 1 .. $entityId - 1], [$entityId], [$entityId + 1 .. $countTotal]

        /** @var SortableEntity[] $entities */
        $entities = [];
        for ($i = 1; $i <= $countTotal; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
        }

        $moveEntity = $entities[$entityId];
        $relyEntity = $entities[$relativeEntityId];

        $moveEntity->moveAfter($relyEntity);
        $this->assertEquals($relativeEntityId + 1, $moveEntity->position);

        // check [1 .. $relativeEntityId] entities
        for ($id = 1; $id <= $relativeEntityId; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id, $entity->position);
        }

        // check  [$relativeEntityId + 1 .. $entityId - 1] entities
        for ($id = $relativeEntityId + 1; $id <= $entityId - 1; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id + 1, $entity->position);
        }

        // check $entityId entity
        $entity = SortableEntity::find($entityId);
        $this->assertEquals($relativeEntityId + 1, $entity->position);

        // check  [$entityId + 1 .. $countTotal] entities
        for ($id = $entityId + 1; $id <= $countTotal; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id, $entity->position);
        }
    }

    /**
     * @param
     * @param
     * @dataProvider moveWhenMovedEntityIsRelativeEntityProvider
     */
    public function testMoveAfterWhenMovedEntityIsRelativeEntity($entityId, $countTotal)
    {

        /** @var SortableEntity[] $entities */
        $entities = [];
        for ($i = 1; $i <= $countTotal; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
        }

        $moveEntity = $entities[$entityId];
        $moveEntity->moveAfter($moveEntity);

        $this->assertEquals($entityId, $moveEntity->position);

        for ($i = 1; $i <= $countTotal; ++$i) {
            $this->assertEquals($i, SortableEntity::find($i)->position);
        }
    }

    /**
     * @param
     * @param
     * @param
     * @dataProvider moveWhenMovedEntityComesBeforeRelativeEntityProvider
     */
    public function testMoveBeforeWhenMovedEntityComesBeforeRelativeEntity($entityId, $relativeEntityId, $countTotal)
    {

        /** @var SortableEntity[] $entities */
        $entities = [];
        for ($i = 1; $i <= $countTotal; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
        }

        $moveEntity = $entities[$entityId];
        $relyEntity = $entities[$relativeEntityId];

        $moveEntity->moveBefore($relyEntity);
        $this->assertEquals($relativeEntityId - 1, $moveEntity->position);

        // check [1 .. $entityId - 1] entities
        for ($id = 1; $id < $entityId; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id, $entity->position);
        }

        // check $entityId entity
        $entity = SortableEntity::find($entityId);
        $this->assertEquals($relativeEntityId - 1, $entity->position);

        // check  [$entityId + 1 .. $relativeEntityId] entities
        for ($id = $entityId + 1; $id <= $relativeEntityId - 1; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id - 1, $entity->position);
        }

        // check  [$relativeEntityId + 1 .. $countTotal] entities
        for ($id = $relativeEntityId; $id <= $countTotal; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id, $entity->position);
        }
    }

    /**
     * @param
     * @param
     * @param
     * @dataProvider moveWhenMovedEntityComesAfterRelativeEntityProvider
     */
    public function testMoveBeforeWhenMovedEntityComesAfterRelativeEntity($entityId, $relativeEntityId, $countTotal)
    {

        /** @var SortableEntity[] $entities */
        $entities = [];
        for ($i = 1; $i <= $countTotal; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
        }

        $moveEntity = $entities[$entityId];
        $relyEntity = $entities[$relativeEntityId];

        $moveEntity->moveBefore($relyEntity);
        $this->assertEquals($relativeEntityId, $moveEntity->position);
        $this->assertEquals($relativeEntityId + 1, $relyEntity->position);

        // check [1 .. $relativeEntityId] entities
        for ($id = 1; $id <= $relativeEntityId - 1; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id, $entity->position);
        }

        // check  [$relativeEntityId + 1 .. $entityId - 1] entities
        for ($id = $relativeEntityId; $id <= $entityId - 1; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id + 1, $entity->position);
        }

        // check $entityId entity
        $entity = SortableEntity::find($entityId);
        $this->assertEquals($relativeEntityId, $entity->position);

        // check  [$entityId + 1 .. $countTotal] entities
        for ($id = $entityId + 1; $id <= $countTotal; ++$id) {
            $entity = SortableEntity::find($id);
            $this->assertEquals($id, $entity->position);
        }
    }

    /**
     * @param
     * @param
     * @dataProvider moveWhenMovedEntityIsRelativeEntityProvider
     */
    public function testMoveBeforeWhenMovedEntityIsRelativeEntity($entityId, $countTotal)
    {

        /** @var SortableEntity[] $entities */
        $entities = [];
        for ($i = 1; $i <= $countTotal; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
        }

        $moveEntity = $entities[$entityId];
        $moveEntity->moveBefore($moveEntity);

        $this->assertEquals($entityId, $moveEntity->position);

        for ($i = 1; $i <= $countTotal; ++$i) {
            $this->assertEquals($i, SortableEntity::find($i)->position);
        }
    }

    public function testSortedScope()
    {
        /** @var SortableEntity[] $entities */
        $entities = [];
        for ($i = 1; $i <= 30; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
        }

        $entities[7]->moveAfter($entities[9]);
        $entities[6]->moveAfter($entities[12]);
        $entities[5]->moveBefore($entities[2]);

        $sortedEntities = SortableEntity::sorted()->get();

        $prevEntityPosition = null;

        foreach ($sortedEntities as $sortedEntity) {
            if ($prevEntityPosition !== null) {
                $this->assertGreaterThan($prevEntityPosition, $sortedEntity->position);
            }
            $prevEntityPosition = $sortedEntity->position;
        }
    }

    /**
     * @param
     * @param
     * @param
     * @dataProvider getPreviousNextEntityProvider
     */
    public function testGetPrevious($entityId, $limit)
    {
        /** @var SortableEntity[] $entities */
        $entities = [];
        for ($i = 1; $i <= 30; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
        }
        /** @var SortableEntity $entity */
        $entity = $entities[$entityId];

        $previous = $entity->getPrevious($limit);

        $expectedCount = $limit ? min($limit, $entityId - 1) : $entityId - 1;
        $this->assertEquals($expectedCount, $previous->count());

        /** @var SortableEntity|null $curr */
        $curr = null;

        $startId = $entityId - $expectedCount;

        foreach ($previous as $prev) {
            $this->assertEquals($startId, $prev->id);
            if ($curr) {
                $this->assertEquals($curr->position + 1, $prev->position);
            }
            $curr = $prev;
            ++$startId;
        }
    }

    /**
     * @param
     * @param
     * @param
     * @dataProvider getPreviousNextEntityProvider
     */
    public function testGetNext($entityId, $limit)
    {
        /** @var SortableEntity[] $entities */
        $entities = [];
        for ($i = 1; $i <= 30; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
        }
        /** @var SortableEntity $entity */
        $entity = $entities[$entityId];

        $next = $entity->getNext($limit);

        $totalNext = count($entities) - $entityId;
        $expectedCount = $limit ? min($totalNext, $limit) : $totalNext;
        $this->assertEquals($expectedCount, $next->count());

        /** @var SortableEntity|null $curr */
        $curr = null;

        $startId = $entityId + 1;

        foreach ($next as $ent) {
            $this->assertEquals($startId, $ent->id);
            if ($curr) {
                $this->assertEquals($curr->position + 1, $ent->position);
            }
            $curr = $ent;
            ++$startId;
        }
    }

    /**
     * @param
     * @param
     * @param
     * @dataProvider getPreviousNextEntityProvider
     */
    public function testDefaultsPrevious($entityId, $limit)
    {
        $entities = [];
        for ($i = 1; $i <= 30; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
        }
        /** @var SortableEntity $entity */
        $entity = $entities[$entityId];

        $expectedEntities = $entity->getPrevious(0);
        $previous = $entity->getPrevious();
        $this->assertEquals($expectedEntities->count(), $previous->count());
        for ($i = 0; $i < $previous->count(); ++$i) {
            $this->assertEquals($expectedEntities->offsetGet($i)->id, $previous->offsetGet($i)->id);
        }
    }

    /**
     * @param
     * @param
     * @param
     * @dataProvider getPreviousNextEntityProvider
     */
    public function testDefaultsNext($entityId, $limit)
    {
        $entities = [];
        for ($i = 1; $i <= 30; ++$i) {
            $entities[$i] = new SortableEntity();
            $entities[$i]->save();
        }
        /** @var SortableEntity $entity */
        $entity = $entities[$entityId];

        $expectedEntities = $entity->getNext(0);
        $next = $entity->getNext();
        $this->assertEquals($expectedEntities->count(), $next->count());
        for ($i = 0; $i < $next->count(); ++$i) {
            $this->assertEquals($expectedEntities->offsetGet($i)->id, $next->offsetGet($i)->id);
        }
    }

    /**
     * @return array
     */
    public function moveWhenMovedEntityComesAfterRelativeEntityProvider()
    {
        return [
            [7, 1, 30],
            [9, 7, 30],
            [30, 15, 30],
        ];
    }

    /**
     * @return array
     */
    public function moveWhenMovedEntityComesBeforeRelativeEntityProvider()
    {
        return [
            [1, 7, 30],
            [7, 9, 30],
            [15, 30, 30],
        ];
    }

    /**
     * @return array
     */
    public function moveWhenMovedEntityIsRelativeEntityProvider()
    {
        return [
            [1, 30],
            [7, 30],
            [30, 30],
        ];
    }

    /**
     * @return array
     */
    public function getPreviousNextEntityProvider()
    {
        return [
            [5, 0],
            [5, 1],
            [1, 1],
            [10, 1],
            [30, 1],
            [5, 12],
            [1, 10],
            [10, 4],
            [30, 4],
        ];
    }
}
