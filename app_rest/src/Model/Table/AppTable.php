<?php

namespace App\Model\Table;

use App\Lib\Validator\AppValidator;
use App\Lib\Validator\ValidationException;
use App\Model\ORM\AppQuery;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;

abstract class AppTable extends Table
{
    use SoftDeleteTrait;

    const TABLE_PREFIX = '';

    protected $_validatorClass = AppValidator::class;

    public static function load()
    {
        $classTableName = namespaceSplit(get_called_class())[1];
        $alias = substr($classTableName, 0, strlen($classTableName) - strlen('Table'));
        /** @var self $table */
        $table = FactoryLocator::get('Table')->get($alias);
        return $table;
    }

    public function getFields(string $alias = null)
    {
        if (!$alias) {
            $alias = $this->_alias;
        }
        $fields = $this->getSchema()->columns();
        foreach ($fields as &$field) {
            $field = $alias . '.' . $field;
        }
        return $fields;
    }

    public function getVisibleColumns(Entity $entity)
    {
        $cols = $this->getSchema()->typeMap();
        $hidden = array_fill_keys($entity->getHidden(), true);

        $res = array_diff_key($cols, $hidden);

        foreach ($entity->getVirtual() as $name) {
            $value = $this->_getVirtualType($entity, $name);
            $res[$name] = $value;
        }
        return $res;
    }

    private function _getVirtualType($entity, $fieldName)
    {
        $value = $entity[$fieldName];
        return gettype($value);
    }

    public function query(): Query
    {
        return new AppQuery($this->getConnection(), $this);
    }

    public function patchEntity(EntityInterface $entity, array $data, array $options = []): EntityInterface
    {
        $res = parent::patchEntity($entity, $data, $options);
        if ($res->getErrors()) {
            throw new ValidationException($entity);
        }
        return $res;
    }
}
