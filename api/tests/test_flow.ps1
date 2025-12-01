<#
Simple PowerShell test script to exercise the API flow:
- Login (cookie)
- Get products
- Add product to cart
- Create order
- Logout

Adjust `$base` if your server uses a different host/path.
#>

$base = 'http://localhost/HKT/api'

Write-Host "Login and save cookies..."
$loginBody = '{"username":"demo","password":"secret"}'
curl -c cookies.txt -H "Content-Type: application/json" -d $loginBody "$base/auth.php" | Write-Output

Write-Host "Get products..."
curl "$base/products.php" | Write-Output

Write-Host "Add to cart (product_id=1)..."
$addBody = '{"product_id":1,"quantity":1}'
curl -b cookies.txt -H "Content-Type: application/json" -d $addBody "$base/cart.php" | Write-Output

Write-Host "Create order (JSON)..."
$orderBody = '{"full_name":"Test User","email":"test@example.com","phone":"0123456789","address":"Hanoi","payment_method":"cod"}'
curl -b cookies.txt -H "Content-Type: application/json" -d $orderBody "$base/orders.php" | Write-Output

Write-Host "Logout..."
curl -b cookies.txt -X POST "$base/logout.php" | Write-Output

Write-Host "Done. Remove cookies file if you want: Remove-Item cookies.txt"
