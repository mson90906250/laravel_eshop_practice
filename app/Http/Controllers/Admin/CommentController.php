<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Common\CustomController;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\MessageBag;

class CommentController extends CustomController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $commentList = $this->paginate($this->search($request), 10)
                                ->withPath(route('admin.comment.index'))
                                ->appends($request->capture()->except('page'));

        return view('admin.comment.index', [
            'commentList' => $commentList
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'id'    => ['required', 'array'],
            'id.*'  => ['required', 'integer', 'exists:comments,id']
        ]);

        if (!Comment::whereIn('id', $validatedData['id'])->delete()) {

            return redirect()->back()->withErrors(new MessageBag(['刪除失敗']));

        }

        return redirect()->back()->with('status', '刪除成功');
    }

    protected function search(Request $request)
    {
        $orderByList = ['user_id', 'product_id', 'updated_at'];

        $validatedData = $request->validate([
            'content'       => ['nullable', 'string'],
            'nickname'      => ['nullable', 'string'],
            'product_name'  => ['nullable', 'string'],
            'date_start'    => ['nullable', 'date'],
            'date_end'      => ['nullable', 'date'],
            'order_by'      => ['nullable', Rule::in($orderByList)]
        ]);

        $query = Comment::from('comments as c');

        $userQuery = User::query();

        $productQuery = Product::query();

        foreach ($validatedData as $attribute => $value) {

            if (!$value) {

                continue;

            }

            switch ($attribute) {

                case 'content':

                    $query->where([
                        ['content', 'like', sprintf('%%%s%%', $value)]
                    ]);

                    break;

                case 'nickname':

                    $userQuery->where([
                        ['nickname', 'like', sprintf('%%%s%%', $value)]
                    ]);

                    break;

                case 'product_name':

                    $productQuery->where([
                        ['name', 'like', sprintf('%%%s%%', $value)]
                    ]);

                    break;

                case 'date_start':

                    $query->where([
                        ['c.updated_at', '>=', $value]
                    ]);

                    break;

                case 'date_end':

                    $query->where([
                        ['c.updated_at', '<=', sprintf('%s 23:59:59', $value)]
                    ]);

                    break;

                case 'order_by':

                    $query->orderBy($value, $request->get('is_asc') ? 'asc' : 'desc');

                    break;


            }

        }

        $query->select(['c.*', 'p.name as product_name', 'u.nickname as user_name'])
                ->joinSub($userQuery, 'u', 'u.id', '=', 'c.user_id')
                ->joinSub($productQuery, 'p', 'p.id', '=', 'c.product_id');

        return $query->get();

    }
}
