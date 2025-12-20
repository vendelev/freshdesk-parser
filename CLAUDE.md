# Development Commands

## Code Quality & Testing

### Running Type Checking (PHPStan)
```bash
cd backend && php vendor/bin/phpstan analyse src --configuration phpstan.dist.neon
```

### Running Code Sniffer (PHP_CodeSniffer)
```bash
cd backend && php vendor/bin/phpcs --standard=phpcs.xml.dist src
```

### Running Tests
```bash
cd backend && php vendor/bin/phpunit
```

### Running All Quality Checks
```bash
cd backend && composer stan && composer cs
```

## Freshdesk Commands

### Parse Freshdesk Tickets (lists all tickets)
```bash
php artisan freshdesk:parse
```

### Load Ticket Details from Freshdesk API
Downloads detailed information for each ticket found in storage/freshdesk/*.json files:
```bash
php artisan freshdesk:load-details
```

This command:
1. Finds all JSON files in `storage/freshdesk/` directory
2. Extracts ticket IDs from each file
3. Calls Freshdesk API `/api/v2/tickets/{id}` for detailed information
4. Saves each ticket to `storage/freshdesk/{YYYY}/{MM}/{ticket_id}.json`

#### Expected Environment Variables
- `FRESHDESK_API_KEY` - Freshdesk API key (from account settings)
- `FRESHDESK_DOMAIN` - Freshdesk domain (e.g., `yourcompany.freshdesk.com`)

#### Output Structure
- ✓ Successful loads: `ticket_id` with status `success`
- ✗ Failed loads: `ticket_id` with status `failed` and error message
