<?php

namespace App\Controller;

use App\Controller\Component\ApiCorsComponent;
use Cake\Controller\Controller;
use Cake\Http\Response;

class ErrorController extends Controller
{
    public function initialize(): void
    {
        ApiCorsComponent::load($this);
    }

    public function render(?string $template = null, ?string $layout = null): Response
    {
        $this->name = 'Error';
        $this->response = parent::render('error_json');
        $this->response = $this->response->withType('json');
        return $this->response;
    }
}
