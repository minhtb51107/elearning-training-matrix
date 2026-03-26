<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Bấm vào 1 thông báo -> Đánh dấu đã đọc -> Chuyển hướng
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        $url = $notification->data['url'] ?? '#';
        return redirect($url !== '#' ? $url : back());
    }

    // Nút "Đánh dấu tất cả đã đọc"
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }
}