<?php

namespace App\Notifications;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class BookPurchased extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Collection $books)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
//        $mail = (new MailMessage)
//                    ->subject('Спасибо за покупку')
//                    ->greeting('Спасибо за покупку!')
//                    ->line('Вы приобрели книги: ');
//
//        foreach ($this->books as $book) {
//            $mail->line($book->title . ' - ' . $book->authors[0]->name . ', ' . $book->published_year);
//        }
//
//        $mail  ->action('Посмотреть на сайте', url('user/favourites'))
//               ->attach(public_path('img/bears.jpg'));
//
//        return $mail;

        return (new MailMessage)
            ->subject('Спасибо за покупку')
            ->subject('Спасибо за покупку')
             ->greeting('Спасибо за покупку!')
           ->line('Вы приобрели книги: ')
            ->action('Посмотреть на сайте', url('user/favourites'))
            ->markdown('emails.purchased', ['books' => $this->books])
            ->attach(public_path('img/bears.jpg'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
