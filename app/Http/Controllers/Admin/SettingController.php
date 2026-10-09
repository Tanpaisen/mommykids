<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use App\Models\Setting;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Hiển thị trang Cài đặt chung.
     */
    public function index()
    {
        $setting = Setting::first();

        if (!$setting) {
            $setting = Setting::create([
                'site_name' => 'MommyKids',
                'hotline'   => '1800 6886',
                'email'     => 'hotro@mommykids.vn',
            ]);
        }

        $menus = AdminMenu::orderBy('order')->get();

        return view(
            'admin.settings.index',
            compact('setting', 'menus')
        );
    }

    /**
     * Cập nhật cài đặt.
     * Logo và favicon được upload lên Cloudinary.
     */
    public function update(Request $request)
    {
        $setting = Setting::first();

        if (!$setting) {
            $setting = Setting::create([
                'site_name' => 'MommyKids',
            ]);
        }

        $request->validate([
            'site_name'        => 'nullable|string|max:255',
            'logo'             => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'favicon'          => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,ico|max:1024',
            'copyright'        => 'nullable|string|max:255',
            'hotline'          => 'nullable|string|max:50',
            'email'            => 'nullable|email|max:255',
            'address'          => 'nullable|string|max:500',
            'facebook_url'     => 'nullable|url|max:255',
            'zalo_url'         => 'nullable|url|max:255',
            'instagram_url'    => 'nullable|url|max:255',
            'meta_description' => 'nullable|string',
            'header_scripts'   => 'nullable|string',
        ]);

        $data = $request->except([
            'logo',
            'favicon',
            '_token',
            '_method',
        ]);

        $oldLogo = $setting->logo;
        $oldFavicon = $setting->favicon;

        /*
        |--------------------------------------------------------------------------
        | Upload LOGO lên Cloudinary
        |--------------------------------------------------------------------------
        */

        try {

            if ($request->hasFile('logo')) {
                $data['logo'] =
                    $this->uploadToCloudinary(
                        $request->file('logo'),
                        'mommykids/settings/logo'
                    );
            }

            if ($request->hasFile('favicon')) {
                $data['favicon'] =
                    $this->uploadToCloudinary(
                        $request->file('favicon'),
                        'mommykids/settings/favicon'
                    );
            }

        } catch (\Throwable $e) {

            Log::error(
                'SETTING CLOUDINARY ERROR',
                [
                    'error' => $e->getMessage(),
                ]
            );

            return back()
                ->withInput()
                ->withErrors([
                    'logo' =>
                        'Không thể tải ảnh lên Cloudinary. '
                        . 'Vui lòng thử lại.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Lưu database
        |--------------------------------------------------------------------------
        */

        $setting->update($data);

        /*
        |--------------------------------------------------------------------------
        | Chỉ xóa ảnh cũ sau khi DB đã lưu thành công
        |--------------------------------------------------------------------------
        */

        // if ($request->hasFile('logo') && $oldLogo) {
        //     $this->deleteStoredImage($oldLogo);
        // }

        // if ($request->hasFile('favicon') && $oldFavicon) {
        //     $this->deleteStoredImage($oldFavicon);
        // }

        Cache::forget('global_settings');

        return back()->with(
            'success',
            'Cập nhật cài đặt hệ thống thành công!'
        );
    }

    /**
     * Cập nhật menu Admin.
     */
    public function updateMenus(Request $request)
    {
        $menusData = $request->input('menus', []);

        foreach ($menusData as $id => $item) {
            AdminMenu::where('id', $id)->update([
                'title'      => $item['title'],
                'group_name' => $item['group_name'] ?? '',
                'order'      => $item['order'] ?? 0,
            ]);
        }

        return back()->with(
            'success',
            'Cập nhật danh sách Menu Admin thành công!'
        );
    }

    /**
     * Khởi tạo Cloudinary.
     */
    private function cloudinary(): Cloudinary
    {
        $cloudUrl = config('cloudinary.cloud_url');

        if (!$cloudUrl) {
            throw new RuntimeException(
                'CLOUDINARY_URL chưa được cấu hình. Kiểm tra .env.'
            );
        }

        return new Cloudinary($cloudUrl);
    }

    /**
     * Upload ảnh lên Cloudinary và trả về URL HTTPS.
     */
    private function uploadToCloudinary(
        UploadedFile $file,
        string $folder
    ): string {
        Log::info('CLOUDINARY_UPLOAD_START', [
            'folder' => $folder,
            'file'   => $file->getClientOriginalName(),
            'size'   => $file->getSize(),
        ]);

        $startedAt = microtime(true);

        try {
            $result = $this->cloudinary()
                ->uploadApi()
                ->upload(
                    $file->getRealPath(),
                    [
                        'folder' => $folder,

                        'resource_type' => 'image',

                        'use_filename' => true,

                        'unique_filename' => true,

                        'overwrite' => false,

                        /*
                        * Cloudinary SDK timeout.
                        * Không cho request nằm chờ 60 giây.
                        */
                        'timeout' => 12,

                        /*
                        * Guzzle:
                        * kết nối tối đa 5 giây.
                        */
                        RequestOptions::CONNECT_TIMEOUT => 5,

                        /*
                        * Tránh một số trường hợp upload
                        * bị chậm vì HTTP Expect: 100-continue.
                        */
                        RequestOptions::EXPECT => false,
                    ]
                );

            Log::info('CLOUDINARY_UPLOAD_OK', [
                'seconds' =>
                    round(
                        microtime(true) - $startedAt,
                        2
                    ),

                'public_id' =>
                    $result['public_id']
                    ?? null,
            ]);

            $url =
                $result['secure_url']
                ?? null;

            if (!$url) {
                throw new RuntimeException(
                    'Cloudinary không trả về secure_url.'
                );
            }

            return $url;

        } catch (\Throwable $e) {

            Log::error('CLOUDINARY_UPLOAD_FAILED', [
                'seconds' =>
                    round(
                        microtime(true) - $startedAt,
                        2
                    ),

                'folder' => $folder,

                'file' =>
                    $file->getClientOriginalName(),

                'error' =>
                    $e->getMessage(),
            ]);

            throw new RuntimeException(
                'Không thể tải ảnh lên Cloudinary: '
                . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Xóa ảnh cũ.
     *
     * Cloudinary URL -> xóa trên Cloudinary.
     * Local cũ -> xóa storage.
     */
    private function deleteStoredImage(?string $image): void
    {
        if (!$image) {
            return;
        }

        if ($this->isCloudinaryUrl($image)) {
            $publicId = $this->extractCloudinaryPublicId($image);

            if (!$publicId) {
                return;
            }

            try {
                $this->cloudinary()
                    ->uploadApi()
                    ->destroy(
                        $publicId,
                        [
                            'resource_type' => 'image',
                            'invalidate'    => true,
                        ]
                    );
            } catch (\Throwable $e) {
                report($e);
            }

            return;
        }

        /*
         * Nếu là URL ngoài nhưng không phải Cloudinary
         * thì không tự xóa.
         */
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return;
        }

        /*
         * Hỗ trợ dữ liệu local cũ:
         *
         * settings/abc.png
         * storage/settings/abc.png
         * public/settings/abc.png
         */
        $cleanPath = ltrim(
            str_replace(
                ['public/', 'storage/'],
                '',
                $image
            ),
            '/'
        );

        Storage::disk('public')->delete($cleanPath);
    }

    private function isCloudinaryUrl(string $url): bool
    {
        $host = parse_url(
            $url,
            PHP_URL_HOST
        );

        if (!is_string($host)) {
            return false;
        }

        return $host === 'res.cloudinary.com'
            || Str::endsWith(
                $host,
                '.cloudinary.com'
            );
    }

    /**
     * Chuyển URL Cloudinary thành public_id để xóa.
     */
    private function extractCloudinaryPublicId(
        string $url
    ): ?string {
        $path = parse_url(
            $url,
            PHP_URL_PATH
        );

        if (!is_string($path)) {
            return null;
        }

        $marker = '/image/upload/';
        $position = strpos(
            $path,
            $marker
        );

        if ($position === false) {
            return null;
        }

        $relativePath = substr(
            $path,
            $position + strlen($marker)
        );

        /*
         * Bỏ version Cloudinary:
         * v1234567890/
         */
        $relativePath = preg_replace(
            '#^v\d+/#',
            '',
            $relativePath
        );

        if (!$relativePath) {
            return null;
        }

        /*
         * Bỏ extension .png/.jpg...
         */
        $publicId = preg_replace(
            '/\.[^\.\/]+$/',
            '',
            $relativePath
        );

        if (!$publicId) {
            return null;
        }

        return rawurldecode(
            ltrim(
                $publicId,
                '/'
            )
        );
    }
}