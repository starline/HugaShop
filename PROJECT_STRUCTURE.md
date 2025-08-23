# HugaShop Project Structure Documentation

## 📋 Project Overview

**HugaShop** is a hybrid CRM/E-commerce platform built with Symfony 7.3 and custom CRM system. It combines traditional Symfony architecture with Eloquent ORM and Smarty templating for maximum flexibility.

**Tech Stack:**
- **Framework**: Symfony 7.3
- **PHP Version**: 8.2+
- **ORM**: Eloquent (Laravel ORM integrated into Symfony)
- **Templates**: Smarty 5.5 (instead of Twig)
- **Frontend**: Bootstrap 5
- **Database**: MySQL with `s_` table prefix
- **Encoding**: UTF8mb4 (for emoji support)

## 🏗️ Root Directory Structure

```
dev.grizlicnc.com.ua/
├── .env / .env_default          # Environment configuration
├── composer.json                # Main dependencies
├── symfony.lock                 # Symfony recipes lock
├── importmap.php               # Asset mapping
├── AGENTS.md                   # AI assistant instructions
├── bin/                        # Symfony console commands
├── config/                     # Symfony configuration
├── public/                     # Web root
├── src/                        # Symfony application code
├── templates/                  # Twig templates (backup)
├── hugashop/                   # Custom CRM system
├── var/                        # Cache, logs, sessions
└── vendor/                     # Composer dependencies
```

## 🎯 Core Symfony Structure (`src/`)

```
src/
├── Kernel.php                  # Symfony kernel
├── Controller/
│   ├── BaseController.php      # Base controller
│   ├── BaseAdminController.php # Admin base controller
│   ├── BaseFrontController.php # Frontend base controller
│   ├── Admin/                  # Admin controllers
│   └── Front/                  # Frontend controllers
├── Services/                   # Business logic services
├── Event/                      # Custom events
├── EventListener/              # Event listeners
└── Twig/                      # Twig extensions
```

## 🛒 HugaShop CRM Structure (`hugashop/`)

### Main CRM (`hugashop/crm/`)

```
hugashop/crm/
├── composer.json               # CRM dependencies
├── config.yaml                 # CRM configuration
├── config_default.yaml         # Default config template
├── Models/                     # Eloquent models
├── Services/                   # CRM business logic
├── Modules/                    # Core modules
│   ├── Delivery/              # Delivery management
│   ├── Payment/               # Payment processing
│   └── Notifier/              # Notifications
├── Addons/                    # System extensions
```

### Libraries (`hugashop/libs/`)

```
hugashop/libs/
├── telegram/                  # Telegram integration
└── turbosms/                 # SMS service integration
```

## ⚙️ Configuration Structure (`config/`)

```
config/
├── bundles.php                # Registered bundles
├── services.yaml              # Service container
├── routes.yaml                # Main routing
├── preload.php               # PHP preloading
├── packages/                  # Package configurations
└── routes/                   # Route definitions
```

## 🌐 Public Structure (`public/`)

```
public/
├── index.php                 # Application entry point
├── favicon.ico               # Site favicon
├── robots.txt               # SEO robots file
├── files/                   # Uploaded files storage
└── tinymce/                 # Rich text editor
```

## 📊 Key Architecture Patterns

### 1. **Hybrid MVC Architecture**
- **Symfony Controllers**: Handle HTTP requests/responses
- **Eloquent Models**: Data layer with custom `$table_fields`
- **Smarty Templates**: Presentation layer

### 2. **Service Layer Pattern**
- Business logic extracted to Service classes
- Dependency injection via Symfony container
- Clean separation of concerns

### 3. **Modular Design**
- Independent modules (Delivery, Payment, Notifier)
- Plugin/Addon system for extensions
- Composer autoloading with PSR-4

### 4. **Event-Driven Architecture**
- Custom events and listeners
- Symfony EventDispatcher integration
- Decoupled component communication

## 🗃️ Database Architecture

### Table Naming Convention
- **Prefix**: `s_` for all tables
- **Charset**: UTF8mb4
- **Collation**: utf8mb4_unicode_ci

### Model Structure
```php
class ExampleModel extends BaseModel {
    protected $table = 's_example';
    
    // Custom field definitions
    public static $table_fields = [
        'id' => ['type' => 'int', 'primary' => true],
        'name' => ['type' => 'varchar', 'length' => 255],
        // ... field definitions
    ];
}
```

## 🔌 Integration Points

### External APIs
- **OpenAI**: AI functionality integration
- **Facebook Business SDK**: Social media advertising
- **Google APIs**: Analytics and advertising
- **reCAPTCHA**: Form protection

### Communication
- **TurboSMS**: SMS notifications
- **Telegram**: Bot integration
- **Mailer**: Email notifications via Symfony Mailer

### Payment Systems
- Modular payment processor architecture
- Support for multiple payment gateways

## 📝 Development Guidelines

### Code Style
- **Indentation**: 4 spaces (tabs)
- **PHP Version**: 8.2+ features encouraged
- **SOLID Principles**: Mandatory
- **PSR Standards**: PSR-4 autoloading, PSR-12 coding style

### Versioning
- Increment `@version` by 0.1 for file changes
- Execute TODO comments when encountered
- Preserve existing comments where possible

### Controller Structure
```php
class ExampleController extends BaseAdminController {
    public function index(): Response {
        // Controller logic
        return $this->render('template.tpl', $data);
    }
}
```

### Service Pattern
```php
class ExampleService {
    public function processData($data): array {
        // Business logic here
        return $processedData;
    }
}
```

## 🚀 Performance Optimizations

### Caching Strategy
- **Smarty Caching**: Configurable template caching
- **Symfony Cache**: Application-level caching
- **Asset Optimization**: Minification enabled

### Image Processing
- **Max Size**: 1920px
- **Quality**: 85%
- **Format Optimization**: WebP support

### Asset Management
- **ImportMap**: Modern asset pipeline
- **Minification**: CSS/JS optimization
- **CDN Ready**: Static file serving

## 🔒 Security Features

### Data Protection
- **Salt-based hashing**: Custom salt configuration
- **CSRF Protection**: Symfony security component
- **Input Validation**: Multi-layer validation

### Authentication
- **Custom Auth System**: No Laravel Breeze/Jetstream
- **Session Management**: Secure session handling
- **Role-based Access**: Admin/Frontend separation

## 📦 Deployment Configuration

### Environment Files
- `.env`: Local environment variables
- `.env_default`: Template for new deployments
- Environment-specific configs supported

### Docker Support
- `compose.override.yaml`: Docker compose configuration
- Development and production profiles

## 🧪 Testing Strategy

### Test Structure
```
tests/
├── Unit/          # Unit tests
├── Integration/   # Integration tests
└── Functional/    # End-to-end tests
```

### Testing Guidelines
- PHPUnit for unit testing
- Symfony test framework integration
- Database testing with transactions

## 📚 Additional Resources

### Documentation Files
- `README.md`: Project overview
- `AGENTS.md`: AI assistant configuration
- `composer.lock`: Dependency versions lock

### IDE Configuration
- `.vscode/`: Visual Studio Code settings
- `.editorconfig`: Cross-editor configuration

---

## 🎯 Quick Start Commands

```bash
# Install dependencies
composer install

# Clear cache
php bin/console cache:clear

# Asset installation
php bin/console assets:install

# Import map installation
php bin/console importmap:install
```

---

*Last Updated: 2025-08-23*
*Version: 1.0*