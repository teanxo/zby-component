<?php

namespace Hyperf\Zby;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response;
use Psr\Http\Message\ResponseInterface;

class ZbyResponse extends Response
{
    /**
     * @param string|null $message
     * @param array|object $data
     * @param int $code
     * @return ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function success(string $message = null, array|object $data = [], int $code = 200): ResponseInterface
    {
        $format = [
            'success' => true,
            'message' => $message ?: '请求成功',
            'code'    => $code,
            'data'    => &$data,
        ];
        $format = $this->toJson($format);
        return $this->getResponse()
            ->withHeader('Server', 'MineAdmin')
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream($format));
    }

    /**
     * @param string $message
     * @param int $code
     * @param array $data
     * @return ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function error(string $message = '', int $code = 500, array $data = []): ResponseInterface
    {
        $format = [
            'success' => false,
            'code'    => $code,
            'message' => $message ?: '请求失败',
        ];

        if (!empty($data)) {
            $format['data'] = &$data;
        }

        $format = $this->toJson($format);
        return $this->getResponse()
            ->withHeader('Server', 'ZbyAdmin')
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream($format));
    }

    /**
     * 向浏览器输出图片
     * @param string $image
     * @param string $type
     * @return ResponseInterface
     */
    public function responseImage(string $image, string $type = 'image/png'): ResponseInterface
    {
        return $this->getResponse()->withHeader('Server', 'MineAdmin')
            ->withAddedHeader('content-type', $type)
            ->withBody(new SwooleStream($image));
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return parent::getResponse(); // TODO: Change the autogenerated stub
    }
}