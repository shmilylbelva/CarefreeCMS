<?php
declare(strict_types=1);

namespace app\service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExportService
{
    /**
     * 导出数据为Excel
     *
     * @param array $data 数据
     * @param array $headers 表头 ['字段名' => '显示名称']
     * @param string $filename 文件名
     * @param string $format 格式 xlsx|csv
     * @return string 文件路径
     */
    public static function export(array $data, array $headers, string $filename, string $format = 'xlsx'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置表头
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);

            // 表头样式
            $sheet->getStyle($col . '1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // 自动调整列宽
            $sheet->getColumnDimension($col)->setAutoSize(true);

            $col++;
        }

        // 填充数据
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach (array_keys($headers) as $field) {
                $value = $item[$field] ?? '';

                // 处理特殊类型
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                } elseif (is_bool($value)) {
                    $value = $value ? '是' : '否';
                }

                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // 保存文件
        $exportPath = runtime_path() . 'export/';
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }

        $filePath = $exportPath . $filename . '.' . $format;

        if ($format === 'csv') {
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(',');
            $writer->setEnclosure('"');
            $writer->setUseBOM(true); // 添加BOM头，避免中文乱码
        } else {
            $writer = new Xlsx($spreadsheet);
        }

        $writer->save($filePath);

        return $filePath;
    }

    /**
     * 导出文章数据
     *
     * @param array $articles 文章数据
     * @param string $format 格式 xlsx|csv
     * @return string 文件路径
     */
    public static function exportArticles(array $articles, string $format = 'xlsx'): string
    {
        $headers = [
            'id' => 'ID',
            'title' => '标题',
            'category_name' => '分类',
            'author_name' => '作者',
            'status' => '状态',
            'is_top' => '置顶',
            'is_recommend' => '推荐',
            'is_hot' => '热门',
            'view_count' => '浏览量',
            'like_count' => '点赞数',
            'publish_time' => '发布时间',
            'create_time' => '创建时间'
        ];

        // 处理数据
        $data = [];
        foreach ($articles as $article) {
            $statusMap = ['0' => '草稿', '1' => '已发布', '2' => '待审核', '3' => '已下线'];

            $data[] = [
                'id' => $article['id'],
                'title' => $article['title'],
                'category_name' => $article['category']['name'] ?? '',
                'author_name' => $article['user']['username'] ?? '',
                'status' => $statusMap[$article['status']] ?? $article['status'],
                'is_top' => $article['is_top'] ? '是' : '否',
                'is_recommend' => $article['is_recommend'] ? '是' : '否',
                'is_hot' => $article['is_hot'] ? '是' : '否',
                'view_count' => $article['view_count'] ?? 0,
                'like_count' => $article['like_count'] ?? 0,
                'publish_time' => $article['publish_time'] ?? '',
                'create_time' => $article['create_time'] ?? ''
            ];
        }

        $filename = 'articles_' . date('YmdHis');
        return self::export($data, $headers, $filename, $format);
    }

    /**
     * 导出分类数据
     *
     * @param array $categories 分类数据
     * @param string $format 格式 xlsx|csv
     * @return string 文件路径
     */
    public static function exportCategories(array $categories, string $format = 'xlsx'): string
    {
        $headers = [
            'id' => 'ID',
            'name' => '名称',
            'slug' => '别名',
            'parent_name' => '父分类',
            'sort' => '排序',
            'article_count' => '文章数',
            'status' => '状态',
            'create_time' => '创建时间'
        ];

        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                'id' => $category['id'],
                'name' => $category['name'],
                'slug' => $category['slug'] ?? '',
                'parent_name' => $category['parent_name'] ?? '顶级分类',
                'sort' => $category['sort'] ?? 0,
                'article_count' => $category['article_count'] ?? 0,
                'status' => $category['status'] ? '启用' : '禁用',
                'create_time' => $category['create_time'] ?? ''
            ];
        }

        $filename = 'categories_' . date('YmdHis');
        return self::export($data, $headers, $filename, $format);
    }

    /**
     * 导出标签数据
     *
     * @param array $tags 标签数据
     * @param string $format 格式 xlsx|csv
     * @return string 文件路径
     */
    public static function exportTags(array $tags, string $format = 'xlsx'): string
    {
        $headers = [
            'id' => 'ID',
            'name' => '名称',
            'slug' => '别名',
            'article_count' => '文章数',
            'sort' => '排序',
            'status' => '状态',
            'create_time' => '创建时间'
        ];

        $data = [];
        foreach ($tags as $tag) {
            $data[] = [
                'id' => $tag['id'],
                'name' => $tag['name'],
                'slug' => $tag['slug'] ?? '',
                'article_count' => $tag['article_count'] ?? 0,
                'sort' => $tag['sort'] ?? 0,
                'status' => $tag['status'] ? '启用' : '禁用',
                'create_time' => $tag['create_time'] ?? ''
            ];
        }

        $filename = 'tags_' . date('YmdHis');
        return self::export($data, $headers, $filename, $format);
    }

    /**
     * 输出文件供下载
     *
     * @param string $filePath 文件路径
     * @param string $downloadName 下载文件名
     */
    public static function download(string $filePath, string $downloadName): void
    {
        if (!file_exists($filePath)) {
            throw new \Exception('文件不存在');
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = $extension === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment;filename="' . $downloadName . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);

        // 删除临时文件
        @unlink($filePath);
        exit;
    }
}
