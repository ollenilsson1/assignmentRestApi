# E-commerce Rest API School Assignment

- [Description](#description)
- [Endpoints](#endpoints)
- [How to use](#how-to-use)

---

## Description

This is an school assignment in PHP to make an Rest API for a e-commerce website, I have used MVC structure with plain PHP (No frameworks).

---

## Endpoints


## USERS
### Create user POST
>http://localhost/storeapi/v1/controller/users.php
### ADMIN - Get all users (GET)
>http://localhost/storeapi/v1/controller/users.php

## SESSIONS
### Log in user (POST)
>http://localhost/storeapi/v1/controller/sessions.php
### Refresh accesstoken (PATCH)
>http://localhost/storeapi/v1/controller/sessions.php?sessionid={yoursessionid}</br>
Use your session id provided when logged in
### Log out user (DELETE)
>http://localhost/storeapi/v1/controller/sessions.php?sessionid={yoursessionid}</br>
Use your session id provided when logged in
## PRODUCTS
### Create product (POST)
>http://localhost/storeapi/v1/controller/products.php
### Get products (GET)
>http://localhost/storeapi/v1/controller/products.php?page={pagenumber}</br>
20 products per page, pagenumber can be 1 or 2 and so on
### Get single product (GET)
>http://localhost/storeapi/v1/controller/products.php?productid={productid}</br>
Need to be a valid productid in URL
### ADMIN - Delete product (DELETE)
>http://localhost/storeapi/v1/controller/products.php?productid={productid}</br>
Need to be a valid productid in URL
### ADMIN - Update product (PATCH)
>http://localhost/storeapi/v1/controller/products.php?productid={productid}</br>
Need to be a valid productid in URL
## CARTS
### User add product to cart (POST)
>http://localhost/storeapi/v1/controller/carts.php?productid={productid}</br>
Need to be a valid productid in URL
### User delete product from cart (DELETE)
>http://localhost/storeapi/v1/controller/carts.php?productid={productid}</br>
Need to be a valid productid in URL
### User delete cart (DELETE)
>http://localhost/storeapi/v1/controller/carts.php
### User get cart (GET)
>http://localhost/storeapi/v1/controller/carts.php
### User checkout cart (POST)
>http://localhost/storeapi/v1/controller/carts.php


[Back To The Top](#Endpoints)


---


## How to use

<details>
<summary>Click here to open how to use</summary>

> You need these programs for the API to work
- Xampp or Mamp - Local database connecion
- Visual Studio Code or other code editor - To open or edit the code
- Postman - To send requests

> Download and use the API
- Step 1 - Clone this repository
- Step 2 - Start Xampp or Mamp and open phpMyAdmin
- Step 3 - Use the SQL script included in the project to create database with the name storedb
- Step 4 - Open Postman and start making requests!

> How to make request in Postman
## USERS
### Create user (POST)
You need to have Content-Type: application/json in header
```html
    {
    "username" : "olle1",
    "fullname" : "Olle Nilsson",
    "password" : "123",
    "email" : "olle.nilsson@medieinstitutet.se"
    }
```
Use endpoint provided
[Create user endpoint](#Create-user-POST)

### Get all users (GET)
You need to have Content-Type: application/json in header<br/>
You need to be logged in and user need to have role = admin in database<br/>
Use the accesstoken provided in header: Authorization = accesstoken
```html
    {
    "username" : "olle1",
    "fullname" : "Olle Nilsson",
    "password" : "123",
    "email" : "olle.nilsson@medieinstitutet.se"
    }
```
Use endpoint provided
[Get all users endpoint](#ADMIN---Get-all-users-(GET))

## SESSIONS
### Log in user (POST)
You need to have Content-Type: application/json in header
```html
    {
    "username" : "olle1",
    "password" : "123"
    }
```
Use endpoint provided
[Log in endpoint](#Log-in-user-(POST))

### Refresh accesstoken (PATCH)
You need to have Content-Type: application/json in header<br/>
Use the accesstoken provided in header: Authorization = accesstoken
```html
    {
    "refresh_token" : "refresh token provided when logged in"
    }
```
Use endpoint provided
[Refresh token endpoint](#Refresh-accesstoken-(PATCH))

### Log out user (DELETE)
Use the accesstoken provided in header: Authorization = accesstoken<br/>
No input needed
Use endpoint provided
[Log out user endpoint](#Log-out-user-(DELETE))

## PRODUCTS
### Create product (POST)
You need to have Content-Type: application/json in header<br/>
You need to be logged in and user need to have role = admin in database<br/>
Use the accesstoken provided in header: Authorization = accesstoken
```html
    {
    "title" : "New product",
    "description" : "Description",
    "imgUrl" : "product.jpg",
    "price" : 170,
    "quantity" : 22
    }
```
Use endpoint provided
[Create product endpoint](#Create-product-(POST))
### Get products (GET)
Use endpoint provided
[Get all products endpoint](#Get-products-(GET))
### Get single product (GET)
Use endpoint provided
[Get single product endpoint](#Get-single-product-(GET))
### Delete single product (DELETE)
You need to be logged in and user need to have role = admin in database<br/>
Use the accesstoken provided in header: Authorization = accesstoken<br/>
Use endpoint provided
[Delete product endpoint](#ADMIN---Delete-product-(DELETE))
### Update product (PATCH)
You need to have Content-Type: application/json in header<br/>
You need to be logged in and user need to have role = admin in database<br/>
Use the accesstoken provided in header: Authorization = accesstoken<br/>
You can update all fields or just one field
```html
    {
    "title" : "New product",
    "description" : "New description",
    "imgUrl" : "New img url",
    "price" : 12,
    "quantity" : 22
    }
```

## CARTS
### User add product to cart (POST)
You need to have Content-Type: application/json in header<br/>
You need to be logged in, Use the accesstoken provided in header: Authorization = accesstoken</br>
Use endpoint provided : 
[Add to cart endpoint](#User-add-product-to-cart-(POST))

### User delete product from cart (DELETE)
You need to have Content-Type: application/json in header<br/>
You need to be logged in, Use the accesstoken provided in header: Authorization = accesstoken</br>
Use endpoint provided : 
[Delete from cart endpoint](#User-delete-product-from-cart-(DELETE))

### User delete cart (DELETE)
You need to have Content-Type: application/json in header<br/>
You need to be logged in, Use the accesstoken provided in header: Authorization = accesstoken</br>
Use endpoint provided : 
[Delete cart endpoint](#User-delete-cart-(DELETE))

### User get cart (GET)
You need to have Content-Type: application/json in header<br/>
You need to be logged in, Use the accesstoken provided in header: Authorization = accesstoken</br>
Use endpoint provided : 
[Get cart endpoint](#User-get-cart-(GET))

### User checkout cart (POST)
You need to have Content-Type: application/json in header<br/>
You need to be logged in, Use the accesstoken provided in header: Authorization = accesstoken</br>
Use endpoint provided : 
[Checkout cart endpoint](#User-checkout-cart-(POST))



[Back To The Top](#How-to-use)


</details>

---

