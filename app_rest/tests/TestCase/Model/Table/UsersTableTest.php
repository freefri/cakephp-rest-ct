<?php
namespace App\Test\TestCase\View\Helper;

use App\Lib\Consts\CacheGrp;
use App\Model\Table\UsersTable;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\UnauthorizedException;
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

    public function testCheckLogin_withEmptyArray(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Email is required');
        $this->Users->checkLogin([]);
    }

    public function testCheckLogin_withoutPassword(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Password is required');
        $this->Users->checkLogin(['email' => 'fake']);
    }

    public function testCheckLogin_withNonExistingEmail(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('User not found');
        $this->Users->checkLogin(['email' => 'fake', 'password' => 'f']);
    }

    public function testCheckLogin_withWrongPassword(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Invalid password');
        $data = [
            'email' => 'test@example.com',
            'password' => 'invalidpass',
        ];
        $this->Users->checkLogin($data);
    }

    public function testCheckLogin(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'passpass',
        ];
        $res = $this->Users->checkLogin($data);
        $this->assertEquals($data['email'], $res->email);
    }
}
