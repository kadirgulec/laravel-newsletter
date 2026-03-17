# Laravel Newsletter Package

A lightweight, database-driven newsletter system for Laravel. It handles subscriptions, unsubscriptions via secure signed URLs, and sends RFC-compliant emails to ensure high deliverability (Gmail/Outlook friendly).

## Features

- 📦 **Database Storage:** Saves subscribers to a local `newsletter_subscribers` table.
- 🔒 **Signed Unsubscribe Links:** Prevents users from unsubscribing others by protecting the route with a cryptographic signature.
- 📧 **RFC Compliance:** Automatically adds `List-Unsubscribe` headers to email responses.
- 🎨 **Ready-to-use Views:** Includes a standard email layout and unsubscribe success page.
- ⚡ **Laravel 10, 11 & 12 Support.**

---

## Installation

### Installing via Composer
```bash
    composer require kadirgulec/newsletter
```

### Post-Installation
After installing, run the migrations to create the subscribers table:
```bash
    php artisan migrate
```
---

## Usage

### 1. The Subscription Form
You can place this HTML form anywhere in your application (footer, sidebar, popup, etc.).
```HTML
    <!-- Display Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @error('email')
        <div class="text-danger">{{ $message }}</div>
    @enderror

    <!-- Subscription Form -->
    <form action="{{ route('newsletter.subscribe') }}" method="POST">
        @csrf
        <input type="email" name="email" placeholder="Enter your email address" required>
        <button type="submit">Subscribe</button>
    </form>
```
### 2. Sending a Newsletter
You can send a newsletter from any Controller, Artisan Command, or Job.
```PHP
    use Illuminate\Support\Facades\Mail;
    use KadirGulec\Newsletter\Models\Subscriber;
    use KadirGulec\Newsletter\Mail\NewsletterMail;

    // 1. Fetch only active subscribers
    $subscribers = Subscriber::active()->get();

    // 2. Iterate and send
    foreach ($subscribers as $subscriber) {
        Mail::to($subscriber->email)->send(
            new NewsletterMail(
                $subscriber,                                // 1. Subscriber Model
                "Weekly Digest: New Updates",               // 2. Email Subject
                "<h1>Hello!</h1><p>Here is the news...</p>" // 3. Email Content (HTML)
            )
        );
    }
    
```

> **Tip:** For large lists, it is highly recommended to queue these emails using Laravel Jobs to prevent your page from timing out.

### 3. Unsubscribing
This process is automated.
1. Every email sent via `NewsletterMail` includes a **Footer Link** and a **Hidden Header** (`List-Unsubscribe`).
2. The link points to a **Signed Route** (`/newsletter/unsubscribe/{id}?signature=...`).
3. If the user clicks it, their status in the database is updated to `is_subscribed = false` and they are shown a success message.

---

## Database Structure

The package creates a table named `newsletter_subscribers`.

| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key |
| `email` | String | Unique email address |
| `is_subscribed` | Boolean | `true` = Active, `false` = Unsubscribed |
| `unsubscribed_at` | Timestamp | Nullable. Date when user unsubscribed |
| `created_at` | Timestamp | Subscription date |

The package also creates a table named `newsletter_campaigns`.

| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key |
| `subject` | String | The email subject |
| `content` | LongText | The HTML content of the email |
| `status` | String | `draft` or `sent` |
| `sent_at` | Timestamp | Nullable. Date when the campaign was sent |

---

## Customization

### Publishing Views
You can publish the email layout and the "Unsubscribe Success" page to your main `resources/views` folder to customize them.
```bash
    php artisan vendor:publish --tag=newsletter-views
```
This will generate:
1. `resources/views/vendor/newsletter/email/standard.blade.php` (The Email Layout)
2. `resources/views/vendor/newsletter/unsubscribe-success.blade.php` (The "You have unsubscribed" page)

### Extending the Model
If you need to add relationships (e.g., linking a subscriber to a `User`), you can extend the model or use the provided one:
```PHP
    use KadirGulec\Newsletter\Models\Subscriber;

    // Check if a specific email is subscribed
    $isSubscribed = Subscriber::where('email', 'john@doe.com')->first()?->is_subscribed;
```
---

## Troubleshooting

**Error: Route [newsletter.subscribe] not defined.**
*   Ensure the service provider is loaded. Run `php artisan route:list | grep newsletter` to check if routes are registered.
*   Try running `php artisan optimize:clear`.

**Error: 403 Invalid Signature on Unsubscribe.**
*   This happens if the URL is modified. Ensure your `APP_URL` in `.env` is set correctly (e.g., `http://localhost:8000` or `https://yourdomain.com`). Signed routes use the `APP_URL` to generate the signature.

---

## Testing

The package includes a comprehensive test suite using PHPUnit and Orchestra Testbench.

### Running Tests

1. Install development dependencies:
```bash
    composer install
```

2. Run the tests:
```bash
    vendor/bin/phpunit
```

The tests cover:
- **Subscriber Model:** Active/Inactive scoping.
- **Newsletter Controller:** Subscription process and welcome email delivery.
- **Unsubscription:** Secure signed URL validation and status updates.
- **Campaigns:** Correct batch queueing to active subscribers only.

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.