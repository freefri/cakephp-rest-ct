<?php

namespace App\Model\Table;

use App\Lib\Consts\CacheGrp;
use App\Model\Entity\User;
use Cake\ORM\TableRegistry;

class UsersTable extends AppTable
{

    public static function load(): UsersTable
    {
        /** @var UsersTable $table */
        $table = TableRegistry::getTableLocator()->get('Users');
        return $table;
    }

    public function getDependentUserIDs($uID): array
    {
        return []; // $this->AdminUsers->getDependentUserIDs($uID);
    }

    private function _getFirst($uid): User
    {
        return $this->findById($uid)
            ->cache('_getFirst' . $uid, CacheGrp::EXTRALONG)
            ->firstOrFail();
    }

    public function getUserGroup($uid): ?int
    {
        $u = $this->_getFirst($uid);
        return $u->group_id ?? null;
    }
}
