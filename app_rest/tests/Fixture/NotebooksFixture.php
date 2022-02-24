<?php

namespace App\Test\Fixture;

use App\Lib\Consts\NotebookShapes;
use Cake\TestSuite\Fixture\TestFixture;

class NotebooksFixture extends TestFixture
{
    const LOAD = 'app.Notebooks';

    public $records = [
        [
            'id' => 1,
            'user_id' => 1,
            'title' => 'Title 1',
            'shape' => NotebookShapes::TODO,
            'created' => '2021-01-18 10:39:23',
            'modified' => '2021-01-18 10:41:31'
        ],
    ];
}
