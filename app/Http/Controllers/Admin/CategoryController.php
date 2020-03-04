<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Common\CustomController;
use Illuminate\Support\MessageBag;

class CategoryController extends CustomController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $categoryList = $this->paginate($this->search($request))
                                ->withPath(route('admin.category.index'))
                                ->appends($request->capture()->except('page'));

        return view('admin.category.index', [
            'categoryList' => $categoryList
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'      => ['required', 'string'],
            'parent_id' => ['bail', 'nullable', 'integer', 'exists:categories,id', function ($attribute, $value, $fail) {

                //選擇的類型parent_id必須為NULL
                $isChild = Category::where([
                    ['id', '=', $value],
                    ['parent_id', '>', 0]
                ])->exists();

                if ($isChild) {

                    $fail('選擇的主類型不能有parent_id');

                }

            }]
        ]);

        if (!Category::create($validatedData)) {

            return redirect()->back()->withErrors(new MessageBag(['創建失敗']));

        }

        return redirect(route('admin.category.index'))->with('status', '創建成功');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        return view('admin.category.edit', [
            'category' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Category $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $validatedData = $request->validate([
            'name'      => ['required', 'string'],
            'parent_id' => ['bail', 'nullable', 'integer', 'exists:categories,id', function ($attribute, $value, $fail) use ($category) {

                //選擇的類型parent_id必須為NULL
                $isChild = Category::where([
                    ['id', '=', $value],
                    ['parent_id', '>', 0]
                ])->exists();

                if ($isChild) {

                    $fail('選擇的主類型不能有parent_id');

                }

                if ($value == $category->id) {

                    $fail('主類型不能選自己');

                }

            }]
        ]);

        if (!$category->update($validatedData)) {

            return redirect()->back()->withErrors(new MessageBag(['修改失敗']));

        }

        return redirect(route('admin.category.index'))->with('status', '修改成功');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'id'    => ['required', 'array'],
            'id.*'  => ['required', 'integer', 'exists:categories,id']
        ]);

        if (!Category::whereIn('id', $validatedData['id'])->delete()) {

            return redirect()->back()->withErrors(new MessageBag(['刪除失敗']));

        }

        return redirect()->back()->with('status', '刪除成功');
    }

    protected function search(Request $request)
    {
        $orderByList = ['id', 'parent_id'];

        $validatedData = $request->validate([
            'name'      => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'id'        => ['nullable', 'integer', 'exists:categories,id'],
            'order_by'  => ['nullable', 'string', Rule::in($orderByList)]
        ]);

        $query = Category::query();

        foreach ($validatedData as $attribute => $value) {

            if ($value == NULL) {

                continue;

            }

            switch ($attribute) {

                case 'name':

                    $query->where([
                        [$attribute, 'like', sprintf('%%%s%%', $value)]
                    ]);

                    break;

                case 'id':
                case 'parent_id':

                    $query->where([
                        [$attribute, '=', $value]
                    ]);

                    break;

                case 'order_by':

                    $query->orderBy($value, $request->get('is_asc') ? 'asc' : 'desc');

                    break;

            }

        }

        return $query->get();
    }
}
