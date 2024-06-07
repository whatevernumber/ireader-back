<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## iBook

RESTful API for a book store. 

API is accessible at http://89.191.225.149

## Stack

Laravel, PostgreSQL, Nginx.

## Description

ISBN is used as primary key for books table.
Both ISBN-10 and ISBN-13 are acceptable.
Before a book is being saved, its ISBN is being validated.

Thumbnails for new books are saved using GoogleBooks API.
Authorization process uses Laravel Sanctum.

Admins can add, update and delete books. Clients may save books in favourites, add 
books to cart and purchase books from their cart (nominally).

Notifications are sent to the client's email using queue after successful purchase.
Each purchase increases client's bonus account that can be spent for future purchases.
