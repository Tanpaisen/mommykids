<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\ProductReadActions;
use App\Http\Controllers\Admin\Concerns\ProductWriteActions;
use App\Http\Controllers\Admin\Concerns\ProductTrashImportActions;
use App\Http\Controllers\Admin\Concerns\ProductHelpers;

class ProductController extends Controller
{
    use ProductReadActions;
    use ProductWriteActions;
    use ProductTrashImportActions;
    use ProductHelpers;
}
