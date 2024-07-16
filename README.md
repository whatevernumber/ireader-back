## iBook

RESTful API for a book store. 

API is accessible at http://89.191.225.149:3000/reader/api

## Stack

PHP 8.2, Laravel 11, PostgreSQL, Manticore, Nginx.

## Description

ISBN is used as primary key for books table. Both ISBN-10 and ISBN-13 are acceptable.
Before a book is saved, its ISBN is validated.

Thumbnails for new books are saved using GoogleBooks API.
Authorization process uses Laravel Sanctum.

Admins can add, update and delete books. Clients may save books in favourites, add 
books to cart and purchase books from their cart (nominally).

Notifications are sent to the client's email using queue after successful purchase.
Each purchase increases client's bonus account that can be spent for future purchases.
