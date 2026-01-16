# IP Address to User ID

## Description

This Matomo plugin allows you to map IP addresses to custom user identifiers. When a visitor accesses your site from a mapped IP address, they will automatically be assigned the configured user ID in Matomo.

This is useful for:
- Identifying internal company users by their office IP addresses
- Assigning meaningful names to known visitors
- Tracking specific network ranges as identified users

## Features

- Map exact IP addresses to user identifiers
- Support for CIDR notation (e.g., `192.168.1.0/24`) to map entire IP ranges
- Bulk import via textarea (one mapping per line)
- Admin interface under Privacy settings
- Automatic user ID assignment during tracking

## Installation

1. Download the plugin
2. Extract to your `plugins/` directory
3. Activate the plugin in Administration > Plugins
4. Go to Administration > Privacy > IP Address to User ID
5. Add your IP to User ID mappings

## Usage

### Adding a Single Mapping

1. Navigate to **Administration > Privacy > IP Address to User ID**
2. Enter the IP address (exact or CIDR range)
3. Enter the user identifier
4. Click **Add**

### Bulk Import

1. Navigate to **Administration > Privacy > IP Address to User ID**
2. In the Bulk Import section, enter mappings in the format:
   ```
   192.168.1.1,John
   192.168.2.0/24,Office
   10.0.0.1,Admin
   ```
3. Click **Import Mappings**

### IP Format Examples

- Exact IP: `192.168.1.100`
- IPv4 Range: `192.168.1.0/24` (matches 192.168.1.0 - 192.168.1.255)
- IPv6: `2001:db8::1`
- IPv6 Range: `2001:db8::/32`

## Requirements

- Matomo 5.0.0 or higher
- Super User access to configure mappings

## Privacy Note

This plugin only applies user IDs to visitors from specifically configured IP addresses. It does not store or process any additional visitor data beyond what Matomo already collects.

## License

GPL v3 or later
