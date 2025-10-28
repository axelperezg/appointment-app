# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 + Filament 4 appointment booking application. Filament provides the admin panel interface for managing appointments, while Laravel handles the backend logic and database operations.

## Development Commands

### Project Setup

```bash
composer setup      # Initial setup: install deps, generate APP_KEY, run migrations, build assets
```

### Development Workflow

```bash
composer dev        # Start all dev services (Laravel serve, queue, logs, Vite hot reload)
```

This runs concurrently:

-   Laravel development server (port 8000)
-   Queue listener (tries=1)
-   Log viewer (pail)
-   Vite dev server with hot module replacement

### Testing

```bash
composer test                                    # Run all tests (clears config cache first)
php artisan test                                 # Run all tests directly
php artisan test tests/Feature/ExampleTest.php   # Run specific test file
php artisan test --filter=testName               # Run tests matching name pattern
```

### Database Operations

```bash
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Fresh database with seeders
php artisan db:seed             # Run seeders only
```

### Frontend Build

```bash
npm run dev         # Development mode with hot reload
npm run build       # Production build
```

## Architecture

### Tech Stack

-   **Backend**: Laravel 12 (PHP 8.2+)
-   **Admin Panel**: Filament 4
-   **Frontend**: Vite + Tailwind CSS 4 + Blade templates
-   **Database**: MySQL (production), SQLite (testing)
-   **Testing**: PHPUnit 11

### Project Structure

```
app/
├── Filament/
│   ├── Resources/    # Auto-discovered Filament CRUD resources
│   ├── Pages/        # Auto-discovered custom admin pages
│   └── Widgets/      # Auto-discovered dashboard widgets
├── Http/Controllers/
├── Models/
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php    # Filament panel configuration
```

### Filament Auto-Discovery

Filament automatically discovers resources, pages, and widgets from:

-   `app/Filament/Resources/` → `App\Filament\Resources` namespace
-   `app/Filament/Pages/` → `App\Filament\Pages` namespace
-   `app/Filament/Widgets/` → `App\Filament\Widgets` namespace

Admin panel accessible at `/admin` with authentication required.

### Configuration Pattern

This project uses Laravel 12's modern configuration approach in `bootstrap/app.php` with fluent API:

```php
Application::configure(basePath: dirname(__DIR__))
    ->withRouting(...)
    ->withMiddleware(...)
    ->withExceptions(...)
    ->create();
```

### Database Architecture

**Core Tables**:

-   `users` - Authentication and user management
-   `cache` - Cache storage
-   `jobs` / `job_batches` / `failed_jobs` - Queue system
-   `sessions` - Session storage
-   `password_reset_tokens` - Auth recovery

**Test Environment**: Uses SQLite in-memory database for fast test execution.

## Development Patterns

### Filament Resource Creation

When creating new Filament resources:

```bash
php artisan make:filament-resource ModelName --generate
```

This creates:

-   Resource class in `app/Filament/Resources/ModelNameResource.php`
-   Associated pages (List, Create, Edit)

### Filament Form Structure

**Always use Sections for form organization:**

Forms should be wrapped in a `Section::make()` component with the following configuration:

```php
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('field1')
                        ->label('Field 1')
                        ->required(),

                    TextInput::make('field2')
                        ->label('Field 2')
                        ->required(),

                    // More fields...
                ]),
        ]);
    }
}
```

**Key Points:**

-   Use `Section::make()` without a title (cleaner UI)
-   Always add `->columnSpanFull()` to make the section span the full width
-   Use `->columns(2)` to distribute fields in 2 columns for better layout
-   All form fields go inside the section's `->schema([])` array
-   This pattern applies to all resource forms in the project

### Model Factory Pattern

Always use factories for test data:

```php
// Create without persisting
$model = Model::factory()->make();

// Create and persist
$model = Model::factory()->create();
```

### Testing with Filament

Filament uses Livewire, so tests start with `livewire()` or `Livewire::test()`:

```php
// Testing table listing
livewire(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable($users->first()->name)
    ->assertCanSeeTableRecords($users->take(1));

// Testing create form
livewire(CreateUser::class)
    ->fillForm(['name' => 'Test', 'email' => 'test@example.com'])
    ->call('create')
    ->assertHasNoFormErrors();
```

### Multiple Panels

If working with multiple Filament panels:

```php
use Filament\Facades\Filament;

Filament::setCurrentPanel('app');
```

## Key Conventions

### Before Making Changes

Before updating files, understand the project structure and how components interact. Review related files to ensure changes are consistent with existing patterns.

### Code Style

Keep code concise and follow existing patterns in the codebase.

### Filament Tables

**Default Behavior** (do not add unless explicitly requested):

-   No bulk actions (`BulkActionGroup::make()`)
-   No column sorting (`->sortable()`)
-   No toggleable columns (`->toggleable()`)

### Queue and Jobs

This application uses database queue driver. When working with jobs:

-   Jobs are stored in `jobs` table
-   Failed jobs tracked in `failed_jobs` table
-   In testing environment, queue connection is `sync` (runs immediately)

### Frontend Assets

-   CSS entry: `resources/css/app.css` (Tailwind via `@import`)
-   JS entry: `resources/js/app.js`
-   Vite watches Blade files and auto-reloads on changes
-   Always run `npm run build` before committing frontend changes

## Common Workflows

### Adding New Feature with CRUD

1. Create migration: `php artisan make:migration create_table_name`
2. Create model: `php artisan make:model ModelName`
3. Create factory: `php artisan make:factory ModelNameFactory`
4. Create Filament resource: `php artisan make:filament-resource ModelName --generate`
5. Run migration: `php artisan migrate`
6. Create tests for the feature
7. Run tests: `php artisan test --filter=ModelName`

### Working with Existing Resources

1. Review the model in `app/Models/`
2. Check the Filament resource in `app/Filament/Resources/`
3. Review related tests in `tests/Feature/`
4. Make changes following existing patterns
5. Run related tests to verify

### Debugging

```bash
composer dev        # Start dev environment with logs visible (pail)
php artisan tinker  # REPL for testing code interactively
```

## Important Notes

-   **Authentication**: Admin panel requires authentication at `/admin/login`
-   **Seeder**: Default test user created via `DatabaseSeeder` (test@example.com)
-   **Composer Scripts**: Use `composer dev` instead of individual `artisan serve` commands to get all dev services
-   **Test Data**: Always use factories, never hardcode test data
-   **Config Caching**: Tests automatically clear config cache before running
