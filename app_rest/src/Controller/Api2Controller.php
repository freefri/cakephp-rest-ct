<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Component\ApiCorsComponent;
use App\Controller\Component\OAuthServerComponent;
use App\Lib\Exception\SilentException;
use App\Lib\I18n\LegacyI18n;
use App\Lib\Oauth\OAuthServer;
use App\Lib\Pdf\Renderer\PdfRenderer;
use Cake\Controller\Controller;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\NotImplementedException;
use Cake\Http\Response;
use Cake\ORM\Entity;
use Cake\View\JsonView;
use Psr\Http\Message\StreamInterface;
use ValidationError;

/**
 * @property OAuthServerComponent $OAuthServer
 */
abstract class Api2Controller extends Controller
{
    const STORE = 'addNew';
    const INDEX = 'getList';
    const SHOW = 'getData';
    const UPDATE = 'edit';
    const PUT = 'put';
    const DESTROY = 'delete';

    public $useJWT = false;
    private $_localOauth = null;
    private static $_hasSwagger = [];

    public $groupRestriction = false;

    protected $return;
    protected $flatResponse = false;
    protected $useOauthServer = true;

    public function isPublicController(): bool
    {
        return false;
    }

    protected function defineMainEntity(): ?Entity
    {
        return null;
    }

    public function getMainEntity(): ?Entity
    {
        if (self::$_hasSwagger[static::cls()] ?? false) {
            return $this->defineMainEntity();
        }
        return null;
    }

    public function docMethodParams(): array
    {
        return [];
    }

    abstract protected function getMandatoryParams();

    protected function setPublicAccess()
    {
        $this->useOauthServer = false;
    }

    private final static function cls(): string
    {
        $className = namespaceSplit(static::class);
        return substr(array_pop($className), 0, -1 * strlen('Controller'));
    }

    public final static function route(bool $hasSwagger = false): array
    {
        self::$_hasSwagger[static::cls()] = $hasSwagger;
        return ['controller' => static::cls(), 'action' => 'main'];
    }

    public function initialize(): void
    {
        if ($this->isPublicController()) {
            $this->setPublicAccess();
        }
        parent::initialize();
        $this->loadModel('Users');
        $this->loadModel('Events');

        ApiCorsComponent::load($this);
        if ($this->useOauthServer) {
            $this->loadComponent('OAuthServer');
        }
    }

    public function beforeFilter(EventInterface $event)
    {
        foreach ($this->getMandatoryParams() as $param) {
            if (!$this->request->is('OPTIONS') && $this->getRequest()->getParam($param) < 1) {
                throw new BadRequestException('Invalid mandatory params in URL');
            }
        }
        $this->_setLanguage();
        parent::beforeFilter($event);
    }

    private function _setLanguage(): void
    {
        $lang = $this->request->getQuery('l');
        if ($lang) {
            LegacyI18n::setLocale(LegacyI18n::convertTo3Letter($lang));
        }
        $lang = $this->request->getHeader('Accept-Language');
        if ($lang && isset($lang[0]) && $lang[0]) {
            if (strlen($lang[0]) === 2) {
                $locale = LegacyI18n::convertTo3Letter($lang[0]);
                LegacyI18n::setLocale($locale);
            }
        }
    }

    public function main($id = null, $secondParam = null)
    {
        $this->_setLanguage();
        $bypass = $this->beforeMain($id, $secondParam);
        if ($bypass) {
            return null;
        }
        $this->_main($id, $secondParam);
    }

    protected function beforeMain($id = null, $secondParam = null)
    {
        return null;
    }

    private function _main($id = null, $secondParam = null)
    {
        if ($this->request->getParam('eventID') && $this->request->getParam('userID')) {
            if (!$this->Event->doesOwnEvent($this->request->getParam('eventID'), $this->request->getParam('userID'))) {
                throw new ForbiddenException('Event does not belong to seller');
            }
        }
        if ($secondParam !== null) {
            throw new NotFoundException('Invalid resource locator');
        }
        if ($id === null) {
            if ($this->request->is('GET')) {
                $this->getList();
            } elseif ($this->request->is('POST')) {
                $this->addNew($this->_getNoEmptyData());
                if ($this->return === false) {
                    $this->response = $this->response->withStatus(204);
                    $this->autoRender = false;
                    return $this->response;
                }
                if (is_array($this->return)) {
                    $this->response->withStatus(201);
                }
            } else {
                throw new MethodNotAllowedException('HTTP method requires ID');
            }
        } else {
            if (!$id) {
                throw new ForbiddenException('Not valid resource id');
            }
            if ($this->request->is('GET')) {
                $this->getData($id);
            } elseif ($this->request->is('PATCH')) {
                $this->edit($id, $this->_getNoEmptyData());
            } elseif ($this->request->is('PUT')) {
                $this->put($id, $this->_getNoEmptyData());
            } elseif ($this->request->is('DELETE')) {
                $this->delete($id);
                if ($this->return === false) {
                    $this->response = $this->response->withStatus(204);
                    $this->autoRender = false;
                    return $this->response;
                }
            } elseif ($this->request->is('HEAD')) {
                throw new SilentException('Method HEAD: not allowed ' . json_encode($_SERVER), 400);
            } else {
                throw new MethodNotAllowedException('MethodNotAllowed ' . json_encode($_SERVER));
            }
        }
        return $this->response;
    }

    private function _getNoEmptyData()
    {
        $data = $this->request->getData();
        if (!$data) {
            $data = $this->_parseInput($this->request->getBody());
            if ($data) {
                return $data;
            }
            throw new BadRequestException('Empty body or invalid Content-Type in HTTP request');
        }
        return $data;
    }

    private function _parseInput(StreamInterface $stream)
    {
        $stream->rewind();
        return $stream->getContents();
    }

    public function beforeRender(EventInterface $event)
    {
        if ($this->return && $this->return instanceof PdfRenderer) {
            /** @var PdfRenderer $ret */
            $ret = $this->return;
            $this->autoRender = false;
            $this->response = $ret->setPdfHeadersForDownload($this->response);
            $this->response = $this->response->withStringBody($ret->render());
            return $this->response;
        } else if ($this->return || $this->return === []) {
            $isOneEntity = $this->return instanceof Entity
                || (count($this->return) == 1 && !$this->return instanceof ResultSetInterface);
            if ($isOneEntity && isset($this->return['meta'])) {
                $meta = $this->return['meta'];
                $this->set(compact('meta'));
                $this->viewBuilder()->setOption('serialize', ['meta']);
            } else {
                $data = $this->return;
                if ($this->flatResponse) {
                    $vars = [];
                    foreach ($data as $k => $d) {
                        $vars[] = $k;
                        $this->set($k, $d);
                    }
                    $this->viewBuilder()->setOption('serialize', $vars);
                } else {
                    $this->set(compact('data'));
                    $this->viewBuilder()->setOption('serialize', ['data']);
                }
            }
        }
    }

    public function render(?string $template = null, ?string $layout = null): Response
    {
        $builder = $this->viewBuilder();
        $builder->setClassName(JsonView::class);
        return parent::render($template, $layout);
    }

    protected function getList()
    {
        throw new NotImplementedException('GET list not implemented yet');
    }

    protected function addNew($data)
    {
        throw new NotImplementedException('POST resource not implemented yet');
    }

    protected function getData($id)
    {
        throw new NotImplementedException('GET resource not implemented yet');
    }

    protected function edit($id, $data)
    {
        throw new NotImplementedException('PATCH not implemented yet');
    }

    protected function put($id, $data)
    {
        throw new NotImplementedException('PUT not implemented yet');
    }

    protected function delete($id)
    {
        throw new NotImplementedException('DELETE not implemented yet');
    }

    protected function here()
    {
        return explode('?', $this->request->getRequestTarget())[0];
    }

    protected function getLocalOauth(): OAuthServer
    {
        if ($this->_localOauth) {
            return $this->_localOauth;
        }
        $this->_localOauth = new OAuthServer();
        $this->_localOauth->setupOauth($this);
        return $this->_localOauth;
    }

    protected function isOwnProvider($userId): bool
    {
        try {
            $uid = $this->getLocalOauth()->verifyAuthorization();
            if ($this->getLocalOauth()->isManagerUser()) {
                return true;
            }
            return $userId == $uid;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function _sanitizeFilename($filename)
    {
        $exploded = explode('.', $filename);
        $extension = array_pop($exploded);
        $filename = implode('.', $exploded);
        return $this->_sanitize($filename) . '.' . $this->_sanitize($extension);
    }

    private function _sanitize($filename)
    {
        $match = array("/\s+/", "/[^a-zA-Z0-9\-]/", "/-+/", "/^-+/", "/-+$/");
        $replace = array("-", "", "-", "", "");
        $string = preg_replace($match, $replace, $filename);
        $string = strtolower($string);
        return $string;
    }

    protected function getFileInfo()
    {
        $uploadedTestFiles = $this->request->getUploadedFiles();
        /** @var \Laminas\Diactoros\UploadedFile $file */
        $file = $uploadedTestFiles['file'] ?? null;
        if (!$file) {
            throw new BadRequestException('File must be provided');
        }
        if ($file->getError() !== UPLOAD_ERR_OK) {
            if ($file->getError() === UPLOAD_ERR_INI_SIZE) {
                throw new ValidationError('upload failed, max ' . ini_get('upload_max_filesize') . 'B', 500);
            }
            throw new InternalErrorException('Error uploading file, code: ' . $file['error'] ?? '');
        }

        $name = $this->_sanitizeFilename($file->getClientFilename());
        $tmpName = TMP . $name;
        $file->moveTo($tmpName);
        return [
            'name' => $name,
            'type' => $file->getClientMediaType(),
            'tmp_name' => $tmpName,
            'error' => $file->getError(),
            'size' => $file->getSize(),
        ];
    }
}
