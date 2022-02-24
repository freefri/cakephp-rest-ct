<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Lib\Consts\NotebookShapes;
use App\Model\Table\NotebooksTable;
use App\Test\Fixture\NotebooksFixture;
use App\Test\Fixture\OauthAccessTokensFixture;

class Api2NotebooksControllerTest extends Api2CommonErrorsTest
{
    public const USERID = 1;

    protected $fixtures = [
        NotebooksFixture::LOAD,
        OauthAccessTokensFixture::LOAD,
    ];

    protected function _getEndpoint(): string
    {
        return '/api/v2/users/' . self::USERID . '/notebooks/';
    }

    public function testAddNew_InputData()
    {
        $data = [
            'title' => 'Title 1',
            'shape' => NotebookShapes::TODO
        ];

        $expected = [
            'id' => 2,
            'user_id' => 1,
            'title' => 'Title 1',
            'shape' => NotebookShapes::TODO,
        ];

        $this->post($this->_getEndpoint(), $data);

        $this->assertResponseOk($this->_getBodyAsString());
        $return = json_decode($this->_getBodyAsString(), true)['data'];
        unset($return['created']);
        unset($return['modified']);
        $this->assertEquals($expected, $return);
    }

    public function testGetNotebook1_ById()
    {
        $this->currentAccessToken = OauthAccessTokensFixture::ACCESS_TOKEN_SELLER;
        $expectedData = [
            'id' => 1,
            'user_id' => 1,
            'title' => 'Title 1',
            'shape' => NotebookShapes::TODO,
            'created' => '2021-01-18T10:39:23+00:00',
            'modified' => '2021-01-18T10:41:31+00:00'
        ];

        $this->get($this->_getEndpoint().'1');
        $this->assertJsonResponseOK(); // TODO not commit
        $return = json_decode($this->_getBodyAsString(), true)['data'];;
        $this->assertEquals($expectedData, $return);
    }

    public function testGetNotebook1_IncorrectUserInURL_ExpectsNotFound()
    {
        $endpoint = '/api/v2/users/2/notebooks/1';

        $this->get($endpoint);

        $this->assertResponseCode(404);
    }

    public function testGetList_GetUser1_GetsSingleUser()
    {
        $expectedData = [
            [
                'id' => 1,
                'user_id' => 1,
                'title' => 'Title 1',
                'shape' => NotebookShapes::TODO,
                'created' => '2021-01-18T10:39:23+00:00',
                'modified' => '2021-01-18T10:41:31+00:00'
            ]
        ];

        $this->get($this->_getEndpoint());

        $this->assertResponseOk($this->_getBodyAsString());
        $return = json_decode($this->_getBodyAsString(), true)['data'];
        $this->assertEquals($expectedData, $return);
    }

    public function testEdit_editNotebook1ChangeTitle()
    {
        $data = [
          'title' => 'Test'
        ];

        $this->patch($this->_getEndpoint() . '1', $data);
        $this->assertResponseOk($this->_getBodyAsString());
        $return = json_decode($this->_getBodyAsString(), true)['data'];
        $this->assertEquals($data['title'], $return['title']);
    }

    public function testEdit_editNotebookFromOtherUser_NotFound()
    {
        $endpoint = '/api/v2/users/2/notebooks/1';
        $data = [
          'title' => 'Test'
        ];

        $this->patch($endpoint, $data);

        $this->assertResponseCode(404);
    }

    public function testEdit_EditInvalidShape_ThrowsException()
    {
        $data = [
            'title' => 'Test',
            'shape' => 'something'
        ];

        $expectedErrorFields =  [
            'shape' => [
                'inList' => 'The provided value is invalid'
            ]
        ];

        $this->patch($this->_getEndpoint() . '1', $data);
        $this->assertResponseError();
        $this->assertResponseCode(400);
        $return = json_decode($this->_getBodyAsString(), true);
        $this->assertEquals($expectedErrorFields, $return['error_fields']);
    }

    public function testDelete_DeletesNotebook1()
    {
        $userId = 1;
        $notebookId = 1;

        $endpoint = '/api/v2/users/'.$userId.'/notebooks/'.$notebookId;

        $this->delete($endpoint);
        $this->assertResponseOk($this->_getBodyAsString());

        $notebook = NotebooksTable::load()->findNotebookByIdAndUser($notebookId, $userId)->first();

        $this->assertNull($notebook);
    }

    public function testDelete_NonExistingNotebook15_Exception()
    {
        $notebookId = 15;
        $this->delete($this->_getEndpoint() . $notebookId);
        $this->assertResponseError($this->_getBodyAsString());
    }

    public function testDelete_DeleteNotebookFromOtherUser_NotFound()
    {
        $endpoint = '/api/v2/users/2/notebooks/1';

        $this->delete($endpoint);
        $this->assertResponseCode(404);
    }
}
