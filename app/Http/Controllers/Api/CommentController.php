<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommentController extends Controller {

    public function getMoreComments(Request $request)
    {
        $validatedData = $request->validate([
            'comment_id'    => ['required', 'integer', 'exists:comments,id'],
            'timestamp'     => ['required', 'integer'],
            'product_id'    => ['required', 'integer', 'exists:products,id']
        ]);

        $commentList = Comment::where([
            ['id', '!=', $validatedData['comment_id']],
            ['product_id', '=', $validatedData['product_id']],
            ['updated_at', '<=', date('Y-m-d H:i:s', $validatedData['timestamp'])]
        ])
        ->limit(10)
        ->get();

        return $commentList->toJson();
    }

}
