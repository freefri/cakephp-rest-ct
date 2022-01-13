<?php
namespace App\Test\TestCase\View\Helper;

use App\Lib\Consts\CacheGrp;
use App\Model\Table\UsersTable;
use Cake\Cache\Cache;
use Cake\TestSuite\Fixture\FixtureStrategyInterface;
use Cake\TestSuite\Fixture\TransactionStrategy;
use Cake\TestSuite\TestCase;

class UsersTableTest extends TestCase
{
    protected $fixtures = ['app.Users'];

    protected function getFixtureStrategy(): FixtureStrategyInterface
    {
        return new TransactionStrategy();
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->Users = UsersTable::load();
    }

    public function testGetUserGroup(): void
    {
        $query = $this->Users->find();
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertNotEmpty($query->all()->toArray(), 'returns not empty');
        $uid = 1;
        $group_id = 3;
        Cache::delete('_getFirst' . $uid, CacheGrp::EXTRALONG);

        $this->assertEquals($group_id, $this->Users->get($uid)->group_id, 'wrong get()');
        $this->assertEquals($group_id, $this->Users->getUserGroup($uid), 'wrong getUserGroup()');
    }
}
