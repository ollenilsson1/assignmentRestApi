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
### POST - Create user
>http://localhost/storeapi/v1/controller/users.php
### <u>ADMIN</u> 
### GET - Get all users
>http://localhost/storeapi/v1/controller/users.php

## SESSIONS
### POST - Log in user
>http://localhost/storeapi/v1/controller/sessions.php
### PATCH - Refresh accesstoken
>http://localhost/storeapi/v1/controller/sessions.php?sessionid={yoursessionid}</br>
Use your session id provided when logged in
### DELETE - Log out user
>http://localhost/storeapi/v1/controller/sessions.php?sessionid={yoursessionid}</br>
Use your session id provided when logged in
## PRODUCTS
### <u>ADMIN</u> 
### POST - Create product
>http://localhost/storeapi/v1/controller/products.php
### GET - Get products
>http://localhost/storeapi/v1/controller/products.php?page={pagenumber}</br>
20 products per page, pagenumber can be 1 or 2 and so on
### GET - Get single product
>http://localhost/storeapi/v1/controller/products.php?productid={productid}</br>
Need to be a valid productid in URL
### <u>ADMIN</u>  
### DELETE - Delete product 
>http://localhost/storeapi/v1/controller/products.php?productid={productid}</br>
Need to be a valid productid in URL
### <u>ADMIN</u>  
### PATCH - Update product
>http://localhost/storeapi/v1/controller/products.php?productid={productid}</br>
Need to be a valid productid in URL
## CARTS
### POST - User add product to cart
>http://localhost/storeapi/v1/controller/carts.php?productid={productid}</br>
Need to be a valid productid in URL
### DELETE - User delete product from cart
>http://localhost/storeapi/v1/controller/carts.php?productid={productid}</br>
Need to be a valid productid in URL
### DELETE - User delete cart 
>http://localhost/storeapi/v1/controller/carts.php
### GET - User get cart 
>http://localhost/storeapi/v1/controller/carts.php
### POST - User checkout cart
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
[Create user endpoint](#POST---Create-user)

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
[Get all users endpoint](#GET---Get-all-users)

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
[Log in endpoint](#POST---Log-in-user)

### Refresh accesstoken (PATCH)
You need to have Content-Type: application/json in header<br/>
Use the accesstoken provided in header: Authorization = accesstoken
```html
    {
    "refresh_token" : "refresh token provided when logged in"
    }
```
Use endpoint provided
[Refresh token endpoint](#PATCH---Refresh-accesstoken)

### Log out user (DELETE)
Use the accesstoken provided in header: Authorization = accesstoken<br/>
No input needed
Use endpoint provided
[Log out user endpoint](#DELETE---Log-out-user)

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
[Create product endpoint](#POST---Create-product)
### Get products (GET)
Use endpoint provided
[Get all products endpoint](#GET---Get-products)
### Get single product (GET)
Use endpoint provided
[Get single product endpoint](#GET---Get-single-product)
### Delete single product (DELETE)
You need to be logged in and user need to have role = admin in database<br/>
Use the accesstoken provided in header: Authorization = accesstoken<br/>
Use endpoint provided
[Delete product endpoint](#DELETE---Delete-product)
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
Use endpoint provided
[Update product endpoint](#PATCH---Update-product)

## CARTS
### User add product to cart (POST)
You need to have Content-Type: application/json in header<br/>
You need to be logged in, Use the accesstoken provided in header: Authorization = accesstoken</br>
Use endpoint provided : 
[Add to cart endpoint](#POST---User-add-product-to-cart)

### User delete product from cart (DELETE)
You need to have Content-Type: application/json in header<br/>
You need to be logged in, Use the accesstoken provided in header: Authorization = accesstoken</br>
Use endpoint provided : 
[Delete from cart endpoint](#DELETE---User-delete-product-from-cart)

### User delete cart (DELETE)
You need to have Content-Type: application/json in header<br/>
You need to be logged in, Use the accesstoken provided in header: Authorization = accesstoken</br>
Use endpoint provided : 
[Delete cart endpoint](#DELETE---User-delete-cart)

### User get cart (GET)
You need to have Content-Type: application/json in header<br/>
You need to be logged in, Use the accesstoken provided in header: Authorization = accesstoken</br>
Use endpoint provided : 
[Get cart endpoint](#GET---User-get-cart)

### User checkout cart (POST)
You need to have Content-Type: application/json in header<br/>
You need to be logged in, Use the accesstoken provided in header: Authorization = accesstoken</br>
Use endpoint provided : 
[Checkout cart endpoint](#POST---User-checkout-cart)



[Back To The Top](#How-to-use)


</details>

---

