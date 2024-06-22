<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Notifications\BookPurchased;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PurchaseController extends Controller
{
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return BookResource::collection($request->user()->purchases()->get());
    }

    /**
     * @throws ConflictHttpException
     * @throws \Exception
     */
    public function buy(Request $request, int $bonus = 0): Response
    {
        $user = $request->user();

        if ($user->bonus < $bonus) {
            throw new ConflictHttpException('Недостаточно бонусов для оплаты', null, 409);
        }

        $booksToBuy = $user->cart()->get();

        if ($booksToBuy->isEmpty()) {
            throw new ConflictHttpException('Нет книг для покупки', null, 409);
        }

        $totalPrice = $booksToBuy->sum('price');

        DB::beginTransaction();

        $totalPrice -= $bonus;
        $user->bonus -= $bonus;

        foreach ($booksToBuy as $book) {
            try {
                $user->cart()->detach($book->isbn);
                $user->purchases()->attach($book, ['price' => $book->price]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw new \Exception($e->getMessage());
            }
        }

        $user->saveBonus($totalPrice);
        $user->save();

        DB::commit();

        try {
            $user->notify(new BookPurchased($booksToBuy));
        } catch (\Exception $e) {
            // logs errors in the separate file
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/notification.log'),
            ])->error($e->getMessage());
        }

        return response('', 200);
    }
}
