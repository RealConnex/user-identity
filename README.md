# realconnex-user-identity
User identity library for parsing JWT token and receiving user identity data

#### Installation

```bash
$ composer require realconnex/user-identity
```
#### Configuration
Register class as a service in service.yml
```yaml
services:
    Realconnex\UserIdentity:
        public: true
```
#### Usage
Inject package into you class
```php
public function __construct(UserIdentity $userIdentity)
{
    $this->userIdentity = $userIdentity;
}
```
Extract user ID from Authorization token 
```php
$response = $this->userIdentity->getUserId();
```
#### Options
- `authServiceName` – parameter allows to replace auth service to receive user data