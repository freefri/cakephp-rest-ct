<?php

namespace App\Model\ORM;

use App\Lib\Exception\SilentException;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Query;

class AppQuery extends Query
{
    const WITH_DELETED = 'with_deleted';

    public function withDeleted(bool $includeDeleted): Query
    {
        $containOptions = $includeDeleted ? [self::WITH_DELETED] : [];
        $this->applyOptions($containOptions);
        return $this;
    }

    public function triggerBeforeFind(): void
    {
        if (!$this->_beforeFindFired && $this->_type === 'select') {
            parent::triggerBeforeFind();
            $repository = $this->getRepository();
            $options = $this->getOptions();
            if (!is_array($options) || !in_array(self::WITH_DELETED, $options)) {
                /** @var \App\Model\Table\AppTable $repository */
                $fieldName = $repository->getSoftDeleteField();
                if ($fieldName) {
                    $aliasedField = $repository->aliasField($fieldName);
                    $this->andWhere($aliasedField . ' IS NULL');
                }
            }
        }
    }

    protected function _execute(): ResultSetInterface
    {
        $this->triggerBeforeFind();
        if ($this->_results) {
            $decorator = $this->_decoratorClass();

            /** @var \Cake\Datasource\ResultSetInterface */
            return new $decorator($this->_results);
        }

        $statement = $this->getEagerLoader()->loadExternal($this, $this->execute());

        return new AppResultSet($this, $statement);
    }

    public function firstOrSilent(string $message)
    {
        $res = $this->first();
        if (!$res) {
            throw new SilentException($message, 404);
        }
        return $res;
    }
}
