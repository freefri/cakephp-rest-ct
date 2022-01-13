<?php

namespace App\Model\ORM;

use Cake\Database\StatementInterface;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;

class AppResultSet extends ResultSet
{
    /** @var InheritanceMarker */
    public $inheritanceMarker;

    public function __construct(Query $query, StatementInterface $statement)
    {
        parent::__construct($query, $statement);
        $repo = $query->getRepository();
        if (property_exists($repo, 'inheritanceMarker')) {
            $this->inheritanceMarker = $repo->inheritanceMarker;
        }

    }
    protected function _groupResult(array $row)
    {
        if ($this->inheritanceMarker) {
            $markerField = $this->_defaultAlias . '__' . $this->inheritanceMarker->getMarkerField();
            if (isset($row[$markerField])) {
                $this->_entityClass = $this->inheritanceMarker->getClassByType($row[$markerField]);
            }

        }
        return parent::_groupResult($row);
    }
}
