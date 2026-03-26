<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $url;

    public function __construct($title, $message, $url = '#')
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
    }

    // Chỉ lưu vào Database (không gửi Email)
    public function via($notifiable)
    {
        return ['database'];
    }

    // Đóng gói cục data đẩy xuống bảng notifications
    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}