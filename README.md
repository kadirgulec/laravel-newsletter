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
This process is automated and prefetch-safe.
1. Every email sent via `NewsletterMail` includes a **Footer Link** and the **`List-Unsubscribe`** + **`List-Unsubscribe-Post: List-Unsubscribe=One-Click`** headers (RFC 8058).
2. The signed URL (`/newsletter/unsubscribe/{id}?signature=...`) responds to both:
   - **`GET`** — renders a confirmation page with a button. Mailbox link-scanners and antivirus prefetchers that hit the URL do **not** unsubscribe the user.
   - **`POST`** — performs the unsubscribe. Used by the confirmation form and by mailbox providers (Gmail, Outlook, …) when the user clicks their built-in "Unsubscribe" UI.
3. On successful unsubscribe, `unsubscribed_at` is set and the user sees the `unsubscribe-success` view.

If the same email later resubscribes through the form, `unsubscribed_at` is cleared and `subscribed_at` is refreshed.

---

## Database Structure

The package creates a table named `newsletter_subscribers`.

| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key |
| `email` | String | Unique email address |
| `subscribed_at` | Timestamp | Nullable. Set when the user subscribes; cleared/unused implies they were never confirmed |
| `unsubscribed_at` | Timestamp | Nullable. Set when the user unsubscribes; `null` means still active |
| `unsubscribe_reason` | Text | Nullable. Optional free-text reason captured at unsubscribe time |
| `created_at` | Timestamp | Row insert date |
| `updated_at` | Timestamp | Row update date |
| `deleted_at` | Timestamp | Nullable. Reserved for soft deletes |

A subscriber is considered active when `subscribed_at IS NOT NULL` and `unsubscribed_at IS NULL`. Use the `Subscriber::active()` scope rather than checking the columns directly.

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
2. `resources/views/vendor/newsletter/confirm-unsubscribe.blade.php` (The "Confirm unsubscribe" page)
3. `resources/views/vendor/newsletter/unsubscribe-success.blade.php` (The "You have unsubscribed" page)

### Extending the Model
If you need to add relationships (e.g., linking a subscriber to a `User`), you can extend the model or use the provided one:
```PHP
    use KadirGulec\Newsletter\Models\Subscriber;

    // Check if a specific email is currently subscribed
    $isSubscribed = Subscriber::active()->where('email', 'john@doe.com')->exists();
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