<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Common\CustomController;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends CustomController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'comment' => ['required', 'string', function ($attribute, $value, $fail) {

                    $size = mb_strlen($value, '8bit');

                    if ($size > 300) {

                        $fail('字數不能超過100');

                    }

                }],
                'product_id' => ['required', 'integer', 'exists:products,id']
            ]);

            $user = Auth::guard('web')->user();

            if (!$user) {

                throw new \ErrorException('用戶不存在');

            }

            $data = [];

            $oldComment = Comment::where([
                ['user_id', '=', $user->id],
                ['product_id', '=', $validatedData['product_id']]
            ])->first();

            if ($oldComment) {

                $updateComment = $oldComment->update([
                    'content' => $validatedData['comment'],
                ]);

                if (!$updateComment) {

                    throw new \ErrorException('評論修改失敗');

                }

                $data['comment'] = $oldComment;

            } else {

                $createComment = Comment::create([
                    'content' => $validatedData['comment'],
                    'user_id' => $user->id,
                    'product_id' => $validatedData['product_id'],
                ]);

                if (!$createComment) {

                    throw new \ErrorException('評論送出失敗');

                }

                $data['comment'] = $createComment;

            }

            $data['user'] = $user;

            return $this->getReturnData(TRUE, 'success', $data);

        } catch (\Exception $e) {

            return $this->getReturnData(FALSE, $e->getMessage());

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {

            $user = Auth::guard('web')->user();

            if (!$user) {

                throw new \ErrorException('用戶不存在');

            }

            $comment = NULL;

            $request->validate([
                'comment' => ['required', 'integer', function ($attribute, $value, $fail) use(&$comment, $user) {

                    $comment = Comment::where([
                        ['id', '=', $value],
                        ['user_id', '=', $user->id]
                    ])->first();

                    if (!$comment) {

                        $fail('此評論不存在');

                    }

                }]
            ]);

            if (!$comment->delete()) {

                throw new \ErrorException('評論刪除失敗');

            }

            return $this->getReturnData(TRUE, 'success');

        } catch (\Exception $e) {

            return $this->getReturnData(FALSE, $e->getMessage());

        }


    }
}
