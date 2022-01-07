<?php

namespace App\Model\Table;

use Cake\Http\Exception\InternalErrorException;

trait SoftDeleteTrait
{
    public function getSoftDeleteField(): ?string
    {
        try {
            return $this->_getSoftDeleteField();
        } catch (InternalErrorException $e) {
            return null;
        }

    }
    private function _getSoftDeleteField(): string
    {
        if (isset($this->softDeleteField)) {
            $field = $this->softDeleteField;
        } else {
            $field = 'deleted';
        }
        if ($this->getSchema()->getColumn($field) === null) {
            throw new InternalErrorException(
                __('Configured field `{0}` is missing from the table `{1}`.',
                    $field,
                    $this->getAlias()
                )
            );
        }
        return $field;
    }

    public function softDelete($primaryKey): void
    {
        $query = $this->query();
        $statement = $query->update()
            ->set([$this->_getSoftDeleteField() => date('Y-m-d H:i:s')])
            ->where(['id' => $primaryKey]);
        $success = $statement->execute()->rowCount() > 0;
        if (!$success) {
            throw new InternalErrorException('Error soft deleting '. $primaryKey);
        }
    }
}
