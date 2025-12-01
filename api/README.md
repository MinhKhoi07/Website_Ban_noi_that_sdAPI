# HKT API (Minimal)

Quick API endpoints added for JSON access. They use the existing `config/connect.php` and PHP sessions for auth.

Endpoints:

- `GET /HKT/api/products.php` — list all products
- `GET /HKT/api/product.php?id=...` — product details
- `POST /HKT/api/auth.php` — login (JSON: `{ "username": "...", "password": "..." }`)
- `GET /HKT/api/cart.php` — get current user's cart (requires session)
- `POST /HKT/api/cart.php` — add product to cart (JSON: `{ "product_id": "...", "quantity": 1 }`)
- `POST /HKT/api/orders.php` — create order (forwards to existing `process_order.php`; supports JSON and multipart/form-data)

Notes:

- Authentication uses existing PHP session mechanism (same session as web UI). Use the `api/auth.php` to create a session.
- `orders.php` forwards the request to the existing `process_order.php` by populating `$_POST` (for JSON) or accepting `multipart/form-data` (for file uploads) and changing working directory. Both JSON and multipart requests are supported.
- CORS: API sends CORS headers by default (allow-all origin). If you need to restrict origins, edit `api/helpers.php` -> `enable_cors()`.

Examples (curl):

Login and keep cookies:

```bash
curl -c cookies.txt -H "Content-Type: application/json" -d '{"username":"demo","password":"secret"}' http://localhost/HKT/api/auth.php
```

Issue token (no cookie) — receive JWT in response:

```bash
curl -H "Content-Type: application/json" -d '{"username":"demo","password":"secret","issue_token":true}' http://localhost/HKT/api/auth.php
```

Use token with Authorization header:

```bash
curl -H "Authorization: Bearer <TOKEN>" http://localhost/HKT/api/cart.php
```

Logout (destroy session):

```bash
curl -b cookies.txt -X POST http://localhost/HKT/api/logout.php
```

Quick automated test (PowerShell):

Run the included test script `api/tests/test_flow.ps1` to exercise login, products, add-to-cart, create order, and logout. Edit `$base` in the script if your server address differs.

Postman collection

1. Import the file `api/tests/postman_collection.json` into Postman (File -> Import -> Choose Files).
2. Set collection variables: `baseUrl` (e.g., `http://localhost/HKT`).
3. To get a JWT, run the "Auth - Issue JWT" request; copy the returned `token` into the collection variable `jwt`.
4. Use the other requests (Get Products, Add To Cart, Create Order, etc.) — they include an `Authorization: Bearer {{jwt}}` header where applicable.

If you prefer cookie/session-based flow, use the "Auth - Login (session)" request and enable Postman's cookie jar for subsequent requests.


Get products:

```bash
curl http://localhost/HKT/api/products.php
```

Add to cart (after login):

```bash
curl -b cookies.txt -H "Content-Type: application/json" -d '{"product_id":1, "quantity":1}' http://localhost/HKT/api/cart.php
```

JSON order (no file upload):

```bash
$body = '{"full_name":"Nguyen A","email":"a@example.com","phone":"0123456789","address":"Hanoi","payment_method":"cod"}'
curl -b cookies.txt -H "Content-Type: application/json" -d $body http://localhost/HKT/api/orders.php
```

Multipart (with payment proof file):

```bash
curl -b cookies.txt -F "full_name=Nguyen A" -F "email=a@example.com" -F "phone=0123456789" -F "address=Hanoi" -F "payment_method=bank_transfer" -F "payment_proof=@path/to/proof.jpg" http://localhost/HKT/api/orders.php
```
