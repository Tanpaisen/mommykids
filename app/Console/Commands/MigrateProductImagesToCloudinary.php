<?php

namespace App\Console\Commands;

use App\Models\Product;
use Cloudinary\Cloudinary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MigrateProductImagesToCloudinary extends Command
{
    protected $signature = 'products:migrate-images-cloudinary';

    protected $description =
        'Upload ảnh sản phẩm local cũ lên Cloudinary và cập nhật URL trong database';

    public function handle(): int
    {
        $this->info('Bắt đầu chuyển ảnh sản phẩm lên Cloudinary...');

        $products = Product::withTrashed()
            ->orderBy('id')
            ->get();

        $total = $products->count();

        $this->info("Tìm thấy {$total} sản phẩm.");

        foreach ($products as $product) {

            $this->newLine();
            $this->info(
                "Sản phẩm #{$product->id}: {$product->name}"
            );

            /*
            |--------------------------------------------------------------------------
            | Ảnh đại diện
            |--------------------------------------------------------------------------
            */

            if ($product->image) {

                if ($this->isRemoteUrl($product->image)) {

                    $this->line(
                        'Ảnh đại diện đã là URL online → bỏ qua.'
                    );

                } else {

                    $newUrl =
                        $this->migrateLocalImage(
                            $product->image,
                            'mommykids/products/main'
                        );

                    if ($newUrl) {

                        $product->image =
                            $newUrl;

                        $this->info(
                            '✓ Đã chuyển ảnh đại diện.'
                        );
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Gallery
            |--------------------------------------------------------------------------
            */

            $gallery =
                $product->images ?? [];

            $newGallery = [];

            foreach ($gallery as $image) {

                if (!$image) {
                    continue;
                }

                /*
                 * URL Cloudinary hoặc URL internet khác
                 * thì giữ nguyên.
                 */
                if ($this->isRemoteUrl($image)) {

                    $newGallery[] =
                        $image;

                    continue;
                }

                $newUrl =
                    $this->migrateLocalImage(
                        $image,
                        'mommykids/products/gallery'
                    );

                if ($newUrl) {

                    $newGallery[] =
                        $newUrl;

                    $this->info(
                        '✓ Đã chuyển 1 ảnh gallery.'
                    );

                } else {

                    /*
                     * Không tìm thấy file local thì
                     * vẫn giữ đường dẫn cũ để không mất dữ liệu.
                     */
                    $newGallery[] =
                        $image;
                }
            }

            $product->images =
                $newGallery;

            /*
             * saveQuietly tránh event không cần thiết.
             */
            $product->saveQuietly();
        }

        $this->newLine();

        $this->info(
            'Hoàn tất chuyển ảnh sản phẩm lên Cloudinary.'
        );

        return self::SUCCESS;
    }

    private function migrateLocalImage(
        string $image,
        string $folder
    ): ?string {
        $image =
            ltrim(
                $image,
                '/'
            );

        /*
         * products/main/abc.jpg
         *
         * sẽ map tới:
         *
         * storage/app/public/products/main/abc.jpg
         */
        if (
            !Storage::disk('public')
                ->exists($image)
        ) {
            $this->warn(
                "Không tìm thấy file local: {$image}"
            );

            return null;
        }

        $fullPath =
            Storage::disk('public')
                ->path($image);

        try {

            $result =
                $this->cloudinary()
                    ->uploadApi()
                    ->upload(
                        $fullPath,
                        [
                            'folder' =>
                                $folder,

                            'resource_type' =>
                                'image',

                            'use_filename' =>
                                true,

                            'unique_filename' =>
                                true,

                            'overwrite' =>
                                false,
                        ]
                    );

            return
                $result['secure_url']
                ?? null;

        } catch (\Throwable $e) {

            $this->error(
                "Upload lỗi: {$image}"
            );

            $this->error(
                $e->getMessage()
            );

            return null;
        }
    }

    private function cloudinary(): Cloudinary
    {
        $cloudUrl =
            config(
                'cloudinary.cloud_url'
            );

        if (!$cloudUrl) {
            throw new RuntimeException(
                'CLOUDINARY_URL chưa được cấu hình.'
            );
        }

        return new Cloudinary(
            $cloudUrl
        );
    }

    private function isRemoteUrl(
        string $value
    ): bool {
        return Str::startsWith(
            $value,
            [
                'http://',
                'https://',
            ]
        );
    }
}