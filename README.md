# Dynamic Blacklist Updater

## Overview
Dynamic Blacklist Updater is a comprehensive WordPress plugin that enables administrators to manage and update blacklist entries dynamically. It simplifies maintaining updated security rules and helps protect your site against unwanted content or malicious activity. With an intuitive admin interface, scheduled background tasks, and integrations with popular form plugins, this tool provides robust control over your site's blacklist functionality.

## Features
- **Dynamic Updates:** Automatically update blacklist entries at configurable intervals.
- **Admin Dashboard:** A modern, user-friendly admin interface for managing blacklist terms and plugin settings.
- **Form Integrations:** Seamless integrations with Contact Form 7, Gravity Forms, Formidable, and WPForms to enforce form validation and security.
- **Input Field Locking:** Prevent accidental edits by locking critical input fields.
- **Real-Time Server Time Display:** Monitor server time directly from the admin panel.
- **CSS & JavaScript Enhancements:** A clean and responsive design powered by custom CSS and supporting JavaScript.
- **Customizable Options:** Flexible settings and extensibility for advanced customization.

## Prerequisites
Before installing the plugin, ensure you have:
- **WordPress:** Version 5.0 or higher.
- **PHP:** Version 7.0 or higher.
- **User Permissions:** Administrative access to your WordPress dashboard.
- **Basic Knowledge:** Familiarity with WordPress plugin management and basic FTP usage (if required).

## Installation
1. **Download or Clone the Repository**
   - Download the ZIP file from GitHub or clone the repository using:
   '''
   git clone https://github.com/yourusername/dynamic-blacklist-updater.git
   '''
2. **Upload to WordPress**
   - If you downloaded the ZIP file, unzip it.
   - Upload the entire `dynamic-blacklist-updater` folder to the `/wp-content/plugins/` directory of your WordPress installation.
3. **Activate the Plugin**
   - Log in to your WordPress admin panel.
   - Navigate to `Plugins > Installed Plugins`.
   - Locate "Dynamic Blacklist Updater" and click on "Activate".

## Configuration
After activation, configure the plugin settings:
- **Access the Admin Page:**
  - Navigate to the plugin's settings page in your WordPress dashboard.
- **Blacklist Management:**
  - Add, edit, or remove blacklist entries manually.
  - Enable automatic updates based on your desired schedule.
- **Schedule Settings:**
  - Define update intervals in the settings. The plugin leverages WordPress cron for scheduled tasks.
- **Form Integration Settings:**
  - Enable and configure integration for your preferred form plugins (Contact Form 7, Gravity Forms, Formidable, WPForms).
- **Appearance Customization:**
  - Modify `admin.css` if you need to adjust the admin interface styling to better match your site's theme.

## Usage
Dynamic Blacklist Updater streamlines the process of managing blacklist data:
- **Managing Blacklist Entries:**
  - Use the admin interface to add new terms, update existing entries, or remove outdated ones.
- **Automated Updates:**
  - Schedule automatic updates so that your blacklist remains current without manual intervention.
- **Form Validation:**
  - With built-in integrations, the plugin validates form submissions to block entries that match the blacklist.
  

Below is a sample code snippet demonstrating how to enqueue the plugin’s scripts and styles:
'''
function dbu_enqueue_scripts() {
    wp_enqueue_style('dbu-admin-style', plugin_dir_url(__FILE__) . 'admin.css');
    wp_enqueue_script('dbu-server-time', plugin_dir_url(__FILE__) . 'server-time.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'dbu_enqueue_scripts');
'''

## File Structure
A breakdown of the plugin's file structure and their purposes:

- **dynamic-blacklist-updater.php:**  
  Main plugin file that initializes the plugin, registers hooks, and sets up the environment.

- **admin-menu.php:**  
  Adds the plugin’s menu and settings pages to the WordPress dashboard.

- **icons.php:**  
  Manages and defines the icons used throughout the admin interface.

- **admin-page.php:**  
  Renders the primary configuration interface where users manage blacklist terms and settings.

- **enqueue.php:**  
  Enqueues necessary CSS and JavaScript files to ensure the admin interface functions correctly.

- **schedules.php:**  
  Sets up and registers scheduled tasks (cron jobs) that handle automatic blacklist updates.

- **tasks.php:**  
  Contains background processing logic, including routines for updating blacklist data.

- **updater.php:**  
  Fetches external data and processes dynamic updates to the blacklist.

- **activation.php:**  
  Executes required setup routines upon plugin activation, such as initializing default settings and scheduling tasks.

- **admin.css:**  
  Custom stylesheet that defines the visual layout and styling for the admin pages.

- **lock_moderation_fields.js:**  
  JavaScript to lock moderation fields, preventing unauthorized changes.

- **lock-input-fields.js:**  
  Implements functionality to disable editing on sensitive input fields.

- **blacklist-toggle.js:**  
  Provides toggle functionality to quickly enable or disable blacklist entries in the UI.

- **server-time.js:**  
  Retrieves and displays the current server time in the admin panel.

- **cf7-validation.php:**  
  Integrates with Contact Form 7 to validate form submissions against the blacklist.

- **gravityforms-validation.php:**  
  Integrates with Gravity Forms for blacklist-based form validation.

- **formidable-validation.php:**  
  Provides validation support for Formidable Forms.

- **wpforms-validation.php:**  
  Integrates WPForms with the blacklist validation system.

## Customization & Extensibility
Dynamic Blacklist Updater is designed with customization in mind:
- **CSS Adjustments:**
  - Edit `admin.css` to modify the look and feel of the admin pages.
- **JavaScript Modifications:**
  - Customize the behavior of UI elements like input locking and toggle buttons by modifying the provided JS files.
- **Extending Functionality:**
  - Developers can add new features or integrations by using hooks and filters provided within the plugin.
  - Modify scheduled tasks in `schedules.php` and `tasks.php` to change update frequencies or add new background processes.
  - The plugin's modular structure makes it easier to integrate with additional form plugins or external APIs.

## Troubleshooting
If you encounter issues, consider the following tips:
- **Plugin Activation Issues:**
  - Double-check that all plugin files are properly uploaded.
  - Look for PHP errors in your server logs.
- **Scheduled Tasks Not Executing:**
  - Verify that WordPress cron is functioning on your site. In some cases, a real cron job may be necessary.
  - Ensure your server’s time settings are accurate.
- **Form Integration Problems:**
  - Confirm that the required form plugins (e.g., Contact Form 7, Gravity Forms, etc.) are installed and active.
  - Review the plugin’s settings to ensure that integration options are enabled.
- **Styling or Script Conflicts:**
  - Check for conflicts with your theme or other plugins that might override CSS or JavaScript functionalities.

## Contributing
Contributions to Dynamic Blacklist Updater are welcome. To contribute:
1. **Fork the Repository:**  
   Create your own fork on GitHub.
2. **Create a New Branch:**  
   Work on your feature or bug fix in a dedicated branch.
3. **Commit Changes:**  
   Ensure your commits are clear and well-documented.
4. **Submit a Pull Request:**  
   Provide a detailed description of your changes and reference any related issues.
   

Please adhere to the coding standards and guidelines provided in the repository. For major changes, consider opening an issue first to discuss your ideas.

## License
This project is licensed under the GPL2 License. 

## Support
For support or further inquiries:
- Open an issue on GitHub with a detailed description of your problem.
- Contact the maintainers via the repository’s communication channels.
- Check the documentation for any known issues or frequently asked questions.

## Acknowledgements
Special thanks to all the contributors, testers, and users who have helped improve Dynamic Blacklist Updater. Your feedback and support are invaluable in making this project better.
