# Administration

The optional reverse-UUID page is available at `/admin/expose` when plugin routes are enabled.
It accepts shortened identifiers, 34-character hexadecimal values starting with `0x`, and uploaded 16-byte binary UUID files.
A normal 36-character UUID already has its readable form and is not a reverse conversion input.
The short-format strategy uses your configured converter and its optional dependency.

The controller extends your application's `AppController`.
Configure authentication and authorization in the application before exposing this page.
The plugin does not provide a separate admin login or grant access to an admin role.

If you load the plugin with `routes: false`, the page is unavailable unless you add the corresponding routes.
Normal Expose behavior and public UUID lookups do not require the admin page.
