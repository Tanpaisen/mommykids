<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{

    public function index(Request $request)
    {
        $query = Faq::query();


        if($request->filled('search')){

            $query->where(function($q) use($request){

                $q->where('question','like','%'.$request->search.'%')
                  ->orWhere('answer','like','%'.$request->search.'%');

            });

        }


        if($request->filled('category')){

            $query->where(
                'category',
                $request->category
            );

        }


        if($request->filled('status')){

            $query->where(
                'is_active',
                $request->status
            );

        }


        $faqs = $query
            ->orderBy('sort_order')
            ->latest()
            ->paginate(10);


        return view(
            'admin.hoi-dap.index',
            compact('faqs')
        );
    }



    public function store(Request $request)
    {

        $data=$request->validate([

            'question'=>'required',
            'answer'=>'required',

        ]);


        Faq::create([

            'question'=>$data['question'],
            'answer'=>$data['answer'],
            'category'=>$request->category ?? 'Chung',
            'is_active'=>1,
            'sort_order'=>$request->sort_order ?? 0,

        ]);


        return back()
            ->with(
                'success',
                'Thêm câu hỏi thành công'
            );

    }



    public function destroy($id)
    {

        Faq::findOrFail($id)->delete();


        return back()
            ->with(
                'success',
                'Đã xóa câu hỏi'
            );

    }



    public function toggle($id)
    {

        $faq = Faq::findOrFail($id);


        $faq->is_active =
            !$faq->is_active;


        $faq->save();


        return back();

    }

}