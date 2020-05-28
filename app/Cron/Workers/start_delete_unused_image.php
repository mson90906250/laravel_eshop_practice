<?php

// //刪除沒使用到的圖片

// use Workerman\Timer;
// use App\Models\Image;
// use Workerman\Worker;
// use Illuminate\Support\Facades\Redis;
// use Illuminate\Support\Facades\Config;

// //處理資料庫誤存的image (即有該筆資料 卻沒有圖片儲存在資料夾)
// $deleteDbImageWorker = new Worker();

// $deleteDbImageWorker->name = 'deleteImageWorker';

// $deleteDbImageWorker->onWorkerStart = function ($worker) {

//     echo setWorkerMessage(sprintf('開始檢查資料庫的圖片是否存在%s', PHP_EOL), $worker);

//     $rootPath = Config::get('custom.product_image_path');

//     $dirName = date('Y-m-d', strtotime('yesterday'));

//     $fullDirPath = sprintf('%s/%s', rtrim($rootPath, '/'), $dirName);

//     Timer::add(1, function () use ($rootPath, $dirName, $fullDirPath, $worker) {

//         $imageQuery = Image::whereBetween('created_at', [date('Y-m-d 00:00:00', strtotime('yesterday')), date('Y-m-d 23:59:59', strtotime('yesterday'))])
//                                 ->orderBy('id', 'asc');

//         //確認要檢查的圖片資料夾是否存在
//         if (!file_exists(public_path($fullDirPath))) {

//             //找不到資料夾 直接刪掉該日期裡所有的圖片
//             $imageQuery->delete();

//             echo setWorkerMessage(sprintf('目標圖片資料夾不存在, 所以從db刪掉該日期裡所有的圖片%s', PHP_EOL), $worker);

//             sleep(3600);

//             return;

//         }

//         //每次檢查十筆資料
//         $lastDeletedImageId = Redis::rpop(Image::REDIS_DB_UNUSED_IMAGE_LIST); //取得上次處理時記錄的最新id

//         $imageQuery->limit(10);

//         if ($lastDeletedImageId) {

//             $imageQuery->where('id', '>', $lastDeletedImageId);

//         }

//         if (!$imageQuery->exists()) {

//             echo setWorkerMessage(sprintf('該日期的所有image記錄已檢查完畢%s', PHP_EOL), $worker);

//             sleep(3600);

//             return;

//         }

//         //確認是否存在該圖片, 或是該圖片沒有對應的product(即沒有product_id), 不存在則刪除;
//         $lastImageId = NULL;

//         foreach ($imageQuery->get() as $image) {

//             $lastImageId = $image->id;

//             if (!file_exists(public_path($image->url)) || !$image->product_id) {

//                 if (!$image->product_id) {

//                     unlink(public_path($image->url));

//                 }

//                 $image->delete();

//                 echo setWorkerMessage(sprintf('Image %d 因不存在此圖片或是沒有product_id 所以被刪除 %s', $image->id, PHP_EOL), $worker);

//             }

//         }

//         Redis::lpush(Image::REDIS_DB_UNUSED_IMAGE_LIST, $lastImageId);

//         //確認資料夾是否爲空
//         if (is_dir_empty(public_path($fullDirPath))) {

//             rmdir(public_path($fullDirPath));

//         }

//     });

// };

// //處理有圖片卻沒有記錄的情況
// $deleteDirImageWorker = new Worker();

// $deleteDirImageWorker->name = 'deleteDirImageWorker';

// $deleteDirImageWorker->onWorkerStart = function ($worker) {

//     echo setWorkerMessage(sprintf('開始處理不在資料庫中的圖片%s', PHP_EOL), $worker);

//     $rootPath = Config::get('custom.product_image_path');

//     $dirName = date('Y-m-d', strtotime('yesterday'));

//     $fullDirPath = sprintf('%s/%s', rtrim($rootPath, '/'), $dirName);

//     Timer::add(1, function () use ($rootPath, $dirName, $fullDirPath, $worker) {

//         //確認目標資料夾是否存在
//         if (!file_exists(public_path($fullDirPath))) {

//             echo setWorkerMessage(sprintf('目標資料夾不存在:%s %s', $dirName, PHP_EOL), $worker);

//             //清除redis
//             Redis::rpop(Image::REDIS_DIR_UNUSED_IMAGE_LIST);

//             sleep(3600);

//             return;

//         }

//         //每1000秒為一個間隔, 取得創建時間為該時間區間内的圖片 (會稍微涵蓋目標日的前一天及隔一天的時間)
//         $time = Redis::rpop(Image::REDIS_DIR_UNUSED_IMAGE_LIST);

//         //確認從redis取出的時間仍在前一天
//         if ($time && sprintf('%d000', $time) >= strtotime(date('Y-m-d 00:00:00'))) {

//             echo setWorkerMessage(sprintf('資料夾中的圖片已檢查完畢 %s', PHP_EOL), $worker);

//             //檢查完畢後 將今天(隔一天)的開始時間塞入redis
//             Redis::lpush(Image::REDIS_DIR_UNUSED_IMAGE_LIST, substr(strtotime(date('Y-m-d 00:00:00')), 0, -3));

//             sleep(3600);

//             return;

//         } else if (!$time) {

//             $time = substr(strtotime('yesterday'), 0, -3);

//         }

//         $imageUrls = glob(sprintf('%s/%s/%d[0-9]*-*.*', public_path($rootPath), $dirName, $time));

//         if (empty($imageUrls)) {

//             $timeStart = date('Y-m-d H:i:s', sprintf('%d000', $time));

//             $timeEnd = date('Y-m-d H:i:s', sprintf('%d999', $time));

//             echo setWorkerMessage(sprintf('搜尋不到創建時間為這個時段的圖片: %s - %s %s', $timeStart, $timeEnd, PHP_EOL), $worker);

//             Redis::lpush(Image::REDIS_DIR_UNUSED_IMAGE_LIST, $time + 1);

//             return;

//         }

//         $imageQuery = Image::query();

//         $uncheckedImages = [];

//         foreach ($imageUrls as $index => $imageUrl) {

//             preg_match('/(\d+-\w+)\.\w+/', $imageUrl, $uncheckedMatches);

//             $uncheckedImages[$uncheckedMatches[0]] = $imageUrl;

//             if ($index == 0) {

//                 $imageQuery->where('url', 'like', sprintf('%%%s%%', $uncheckedMatches[1]));

//             } else {

//                 $imageQuery->orWhere('url', 'like', sprintf('%%%s%%', $uncheckedMatches[1]));

//             }

//         }

//         $images = $imageQuery->get();

//         if (!$images->isEmpty()) {

//             foreach ($images->pluck('url') as $url) {

//                 preg_match('/(\d+-\w+)\.\w+/', $url, $matches);

//                 unset($uncheckedImages[$matches[0]]);

//             }

//         }

//         //刪除不在資料庫的圖片
//         foreach ($uncheckedImages as $uncheckedImage) {

//             unlink($uncheckedImage);

//             echo setWorkerMessage(sprintf('已刪除圖片:%s %s', $uncheckedImage, PHP_EOL), $worker);

//         }


//         //確認資料夾是否為空
//         if (is_dir_empty(public_path($fullDirPath))) {

//             rmdir(public_path($fullDirPath));

//             echo setWorkerMessage(sprintf('刪除空資料夾:%s %s', $dirName, PHP_EOL), $worker);

//         }

//         Redis::lpush(Image::REDIS_DIR_UNUSED_IMAGE_LIST, $time + 1);

//     });

// };

// function setWorkerMessage($message,Worker $worker)
// {
//     return sprintf('%s->%s', $worker->name, $message);
// }


