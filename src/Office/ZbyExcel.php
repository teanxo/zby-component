<?php

namespace Hyperf\Zby\Office;



use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Zby\Exception\ZbyException;
use Hyperf\Zby\Interfaces\ZbyModelExcel;
use Hyperf\Zby\ZbyResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class ZbyExcel
{
    // 注解所在路径
    public const ANNOTATION_NAME = 'Hyperf\Zby\Annotation\ExcelProperty';

    /**
     * @var array|null
     */
    protected ?array $annotationMate;

    /**
     * @var array
     */
    protected array $property = [];
    protected array $dictData = [];

    /**
     * @param String $dto
     * @param ZbyModelExcel $model
     */
    public function __construct(string $dto)
    {
        if (!(new $dto) instanceof ZbyModelExcel) {
            throw new ZbyException('dto does not implement an interface of the ZbyModelExcel', 500);
        }
        $dtoObject = new $dto();
        if (method_exists($dtoObject, 'dictData')) {
            $this->dictData = $dtoObject->dictData();
        }
        $this->annotationMate = AnnotationCollector::get($dto);
        $this->parseProperty();
    }

    /**
     * @return array
     */
    public function getProperty(): array
    {
        return $this->property;
    }

    /**
     * @return array
     */
    public function getAnnotationInfo(): array
    {
        return $this->annotationMate;
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \RedisException
     */
    protected function parseProperty(): void
    {
        if (empty($this->annotationMate) || !isset($this->annotationMate['_c'])) {
            throw new ZbyException('dto annotation info is empty', 500);
        }

        $index = 0;
        foreach ($this->annotationMate['_p'] as $name => $mate) {
            $z_index = isset($mate[self::ANNOTATION_NAME]->index) ? $mate[self::ANNOTATION_NAME]->index : $index;
            $this->property[$z_index] = [
                'name' => $name,
                'value' => $mate[self::ANNOTATION_NAME]->value,
                'width' => $mate[self::ANNOTATION_NAME]->width ?? null,
                'align' => $mate[self::ANNOTATION_NAME]->align ?? null,
                'headColor' => $mate[self::ANNOTATION_NAME]->headColor ?? null,
                'headBgColor' => $mate[self::ANNOTATION_NAME]->headBgColor ?? null,
                'color' => $mate[self::ANNOTATION_NAME]->color ?? null,
                'bgColor' => $mate[self::ANNOTATION_NAME]->bgColor ?? null,
                'dictData' => $mate[self::ANNOTATION_NAME]->dictData,
                'dictName' => empty($mate[self::ANNOTATION_NAME]->dictName) ? null : '',
                'path' => $mate[self::ANNOTATION_NAME]->path ?? null,
            ];
            $index++;
        }
        ksort($this->property);
    }

    /**
     * 下载excel
     * @param string $filename
     * @param string $content
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function downloadExcel(string $filename, string $content): \Psr\Http\Message\ResponseInterface
    {
        return ApplicationContext::getContainer()->get(ZbyResponse::class)->getResponse()
            ->withHeader('Server', 'MineAdmin')
            ->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('content-disposition', "attachment; filename={$filename}; filename*=UTF-8''" . rawurlencode($filename))
            ->withHeader('content-transfer-encoding', 'binary')
            ->withHeader('pragma', 'public')
            ->withBody(new SwooleStream($content));
    }

    /**
     * 获取 excel 列索引
     * @param int $columnIndex
     * @return string
     */
    protected function getColumnIndex(int $columnIndex = 0): string
    {
        if ($columnIndex < 26) {
            return chr(65 + $columnIndex);
        } else if ($columnIndex < 702) {
            return chr(64 + ($columnIndex / 26)) . chr(65 + $columnIndex % 26);
        } else {
            return chr(64 + (($columnIndex - 26) / 676)) . chr(65 + ((($columnIndex - 26) % 676) / 26)) . chr(65 + $columnIndex % 26);
        }
    }
}